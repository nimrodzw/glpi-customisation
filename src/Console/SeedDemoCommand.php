<?php

/**
 * FrexCore demo seeder.
 *
 * Builds a believable organisation inside a tenant: branches, staff, a named
 * server estate, categories carrying the regulation behind them, and a year
 * of ticket history with real shape.
 *
 * WHY A CONSOLE COMMAND RATHER THAN SQL
 * The obvious approach is a .sql file of INSERTs, and it is the wrong one.
 * Tickets are not a single table: actors live in glpi_tickets_users, and the
 * application maintains history and derived fields alongside. Hand-written
 * SQL that misses those produces a demo where every ticket has no requester,
 * which is noticed in the first thirty seconds of a presentation. Running
 * inside the console means the application's own object layer does the work
 * and all of that stays correct.
 *
 * SAFETY
 * This command writes data. Run against a live tenant it would put invented
 * policyholders into a real client's system. It therefore refuses to touch
 * any instance that already holds tickets unless explicitly forced, supports
 * --dry-run, and tags everything it creates so --purge removes exactly what
 * it made and nothing a person added by hand.
 *
 * @copyright Copyright (C) 2026 FrexCore and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace GlpiPlugin\Frexcore\Console;

use Glpi\Console\AbstractCommand;
use GlpiPlugin\Frexcore\Demo\DemoData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SeedDemoCommand extends AbstractCommand
{
    private bool $dry = false;
    private int $entity = 0;
    private array $made = [];

    protected function configure(): void
    {
        parent::configure();
        $this->setName('plugins:frexcore:seed-demo');
        $this->setDescription('Load the FrexCore demonstration dataset into this instance');

        $this->addOption('org', null, InputOption::VALUE_REQUIRED,
            'Organisation name shown throughout the demo', 'Shiloh Financial Group');
        $this->addOption('tickets', null, InputOption::VALUE_REQUIRED,
            'How many tickets to generate across the last 12 months', '420');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE,
            'Report what would be created and write nothing');
        $this->addOption('purge', null, InputOption::VALUE_NONE,
            'Remove previously seeded demo records and stop');
        $this->addOption('force', null, InputOption::VALUE_NONE,
            'Proceed even though this instance already holds tickets');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        $this->dry = (bool) $input->getOption('dry-run');
        $org       = (string) $input->getOption('org');
        $target    = max(20, (int) $input->getOption('tickets'));

        if ($input->getOption('purge')) {
            return $this->purge($output);
        }

        // Guard. Seeded data in a client's live instance is not an
        // inconvenience, it is invented people in real records.
        $existing = (int) ($this->db->request([
            'COUNT' => 'c', 'FROM' => 'glpi_tickets', 'WHERE' => ['is_deleted' => 0],
        ])->current()['c'] ?? 0);

        if ($existing > 0 && !$input->getOption('force') && !$this->dry) {
            $output->writeln("<error>This instance already holds {$existing} ticket(s).</error>");
            $output->writeln('Seeding would mix demonstration records into real data.');
            $output->writeln('Use --dry-run to preview, or --force if this really is a demo instance.');
            return self::FAILURE;
        }

        // Seeding creates hundreds of records. With notifications live that
        // is hundreds of emails to people who do not exist.
        $CFG_GLPI['use_notifications'] = 0;
        $CFG_GLPI['notifications_mailing'] = 0;

        $output->writeln('');
        $output->writeln("  <info>FrexCore demonstration data</info>");
        $output->writeln("  organisation   {$org}");
        $output->writeln("  tickets        {$target} across the last 12 months");
        $output->writeln("  mode           " . ($this->dry ? 'DRY RUN, nothing written' : 'writing'));
        $output->writeln('');

        $locations  = $this->seedLocations($output);
        $groups     = $this->seedGroups($output);
        $users      = $this->seedUsers($output, $locations, $groups);
        $categories = $this->seedCategories($output);
        $this->seedServers($output, $locations);
        $this->seedTickets($output, $target, $users, $categories);

        $output->writeln('');
        foreach ($this->made as $what => $n) {
            $output->writeln(sprintf('  %-26s %d', $what, $n));
        }
        $output->writeln('');
        $output->writeln($this->dry
            ? '  <comment>Dry run. Nothing was written. Re-run without --dry-run to apply.</comment>'
            : '  <info>Done. Remove it again with --purge.</info>');
        $output->writeln('');

        return self::SUCCESS;
    }

    // ----------------------------------------------------------------- bits

    private function tally(string $what, int $n = 1): void
    {
        $this->made[$what] = ($this->made[$what] ?? 0) + $n;
    }

    /**
     * Create a record unless it already exists, so a re-run tops up rather
     * than duplicating. Everything carries the marker in its comment so the
     * purge can find it later.
     */
    private function ensure(string $class, array $fields, array $unique): int
    {
        $table = getTableForItemType($class);

        // The caller names the columns that identify a record. Deriving the
        // lookup from a fixed set instead would break on glpi_users, which
        // does not carry entities_id the way the other tables do.
        $where = array_intersect_key($fields, array_flip($unique));

        $found = $this->db->request(['SELECT' => 'id', 'FROM' => $table, 'WHERE' => $where])->current();
        if ($found) {
            return (int) $found['id'];
        }
        if ($this->dry) {
            return -1;
        }

        $item = new $class();
        $id = $item->add($fields);
        return $id === false ? -1 : (int) $id;
    }

    // -------------------------------------------------------------- seeding

    private function seedLocations(OutputInterface $o): array
    {
        $ids = [];
        foreach (DemoData::LOCATIONS as $loc) {
            $ids[$loc['name']] = $this->ensure(\Location::class, [
                'name'        => $loc['name'],
                'country'     => $loc['country'],
                'entities_id' => $this->entity,
                'comment'     => DemoData::MARKER,
            ], ['name', 'entities_id']);
            $this->tally('locations');
        }
        $o->writeln('  locations');
        return $ids;
    }

    private function seedGroups(OutputInterface $o): array
    {
        $ids = [];
        foreach (DemoData::DEPARTMENTS as $dept) {
            $ids[$dept] = $this->ensure(\Group::class, [
                'name'        => $dept,
                'entities_id' => $this->entity,
                'is_recursive'=> 1,
                'comment'     => DemoData::MARKER,
            ], ['name', 'entities_id']);
            $this->tally('departments');
        }
        $o->writeln('  departments');
        return $ids;
    }

    private function seedUsers(OutputInterface $o, array $locations, array $groups): array
    {
        $locNames = array_keys($locations);
        $ids = [];
        foreach (DemoData::PEOPLE as $i => [$first, $last, $dept, $isAgent]) {
            $login = strtolower($first . '.' . $last);
            $loc   = $locNames[$i % count($locNames)];

            $id = $this->ensure(\User::class, [
                'name'         => $login,
                'realname'     => $last,
                'firstname'    => $first,
                'locations_id' => max(0, $locations[$loc] ?? 0),
                'entities_id'  => $this->entity,
                'is_active'    => 1,
                'comment'      => DemoData::MARKER,
            ], ['name']);

            if ($id > 0 && !$this->dry) {
                // Email lives in its own table, and a user without one looks
                // unfinished the moment a ticket is opened on their behalf.
                $email = new \UserEmail();
                if (!$this->db->request([
                        'FROM' => 'glpi_useremails', 'WHERE' => ['users_id' => $id],
                    ])->current()) {
                    $email->add([
                        'users_id'   => $id,
                        'is_default' => 1,
                        'email'      => $login . '@shilohgroup.example',
                    ]);
                }
                if (($groups[$dept] ?? 0) > 0 && !$this->db->request([
                        'FROM' => 'glpi_groups_users',
                        'WHERE' => ['users_id' => $id, 'groups_id' => $groups[$dept]],
                    ])->current()) {
                    (new \Group_User())->add(['users_id' => $id, 'groups_id' => $groups[$dept]]);
                }
            }

            $ids[] = ['id' => $id, 'agent' => $isAgent];
            $this->tally('people');
        }
        $o->writeln('  people and departments linked');
        return $ids;
    }

    private function seedCategories(OutputInterface $o): array
    {
        $ids = [];
        foreach (DemoData::CATEGORIES as [$name, $group, $basis]) {
            // The obligation goes in the comment rather than the name, so the
            // queue stays readable while the reference is one hover away.
            $ids[$name] = $this->ensure(\ITILCategory::class, [
                'name'        => $name,
                'comment'     => $basis . ' ' . DemoData::MARKER,
                'entities_id' => $this->entity,
                'is_recursive'=> 1,
            ], ['name', 'entities_id']);
            $this->tally('categories');
        }
        $o->writeln('  categories, each carrying its regulatory basis');
        return $ids;
    }

    private function seedServers(OutputInterface $o, array $locations): void
    {
        $dc = $locations['Data centre'] ?? 0;
        foreach (DemoData::SERVERS as [$host, $role]) {
            $this->ensure(\Computer::class, [
                'name'         => $host,
                'comment'      => $role . ' ' . DemoData::MARKER,
                'locations_id' => max(0, $dc),
                'entities_id'  => $this->entity,
            ], ['name', 'entities_id']);
            $this->tally('servers');
        }
        $o->writeln('  server estate');
    }

    private function seedTickets(OutputInterface $o, int $target, array $users, array $categories): void
    {
        $templates = DemoData::TICKETS;
        $agents    = array_values(array_filter($users, fn($u) => $u['agent'] && $u['id'] > 0));
        $staff     = array_values(array_filter($users, fn($u) => $u['id'] > 0));

        if (!$this->dry && (empty($agents) || empty($staff))) {
            $o->writeln('  <error>no usable people, skipping tickets</error>');
            return;
        }

        $now = time();
        $made = 0;

        for ($i = 0; $i < $target; $i++) {
            [$cat, $title, $body, $urgency, $isRequest] = $templates[$i % count($templates)];

            // Spread over twelve months, weighted towards recent so the trend
            // chart rises rather than sitting flat. A flat year of identical
            // volume is the shape no real service desk has ever had.
            $skew    = ($i / max(1, $target)) ** 0.7;
            $daysAgo = (int) round(365 * (1 - $skew)) + random_int(0, 6);
            $opened  = $now - ($daysAgo * 86400) - random_int(0, 43200);

            // Nearly all older work is closed. Leaving a lot of it open would
            // be the realistic-looking choice that is actually wrong: an old
            // open ticket is a breached ticket, so a backlog of them makes the
            // demo organisation look like it is failing rather than coping.
            $closed = $daysAgo > 30 ? (random_int(1, 100) <= 99)
                                    : (random_int(1, 100) <= 55);

            $status = $closed
                ? \CommonITILObject::CLOSED
                : [\CommonITILObject::INCOMING, \CommonITILObject::ASSIGNED,
                   \CommonITILObject::PLANNED, \CommonITILObject::WAITING][random_int(0, 3)];

            $requester = $staff[random_int(0, count($staff) - 1)]['id'];
            $assignee  = $agents[random_int(0, count($agents) - 1)]['id'];

            if ($this->dry) { $made++; continue; }

            $fields = [
                'name'                => $title,
                'content'             => $body,
                'entities_id'         => $this->entity,
                'type'                => $isRequest ? \Ticket::DEMAND_TYPE : \Ticket::INCIDENT_TYPE,
                'itilcategories_id'   => max(0, $categories[$cat] ?? 0),
                'status'              => $status,
                'urgency'             => $urgency,
                'impact'              => max(1, min(5, $urgency - random_int(0, 1))),
                'priority'            => $urgency,
                '_users_id_requester' => $requester,
                '_users_id_assign'    => $assignee,
                '_auto_import'        => true,
            ];

            $ticket = new \Ticket();
            $id = $ticket->add($fields);
            if ($id === false) {
                continue;
            }

            // Dates are set after creation on purpose. The application stamps
            // its own on insert, so backdating has to be a second step; these
            // are plain columns, so writing them directly is safe.
            $open = date('Y-m-d H:i:s', $opened);
            $upd  = ['date' => $open, 'date_creation' => $open, 'date_mod' => $open];

            if ($closed) {
                $solveIn = random_int(1800, 4 * 86400);
                $upd['solvedate'] = date('Y-m-d H:i:s', $opened + $solveIn);
                $upd['closedate'] = date('Y-m-d H:i:s', $opened + $solveIn + random_int(600, 86400));
                $upd['date_mod']  = $upd['closedate'];
            } else {
                // Resolution targets run from now, not from the open date.
                // Deriving them from the open date made almost every open
                // ticket breached the moment it aged, which reads as an
                // organisation in collapse rather than one worth copying.
                //
                // A deliberate minority is genuinely late, because a board
                // showing no breaches at all fails to demonstrate the single
                // thing a service desk is bought to prevent.
                if (random_int(1, 100) <= 12) {
                    $due = $now - random_int(3600, 3 * 86400);
                } else {
                    $due = $now + (($urgency >= 4 ? random_int(1, 8) : random_int(8, 72)) * 3600);
                }
                $upd['time_to_resolve'] = date('Y-m-d H:i:s', $due);
            }

            $this->db->update('glpi_tickets', $upd, ['id' => $id]);

            // A closed ticket with nothing in the resolution field is the
            // first thing a prospect opens and the first thing that looks
            // unfinished. Solutions are their own records, not a column.
            if ($closed) {
                try {
                    (new \ITILSolution())->add([
                        'itemtype' => 'Ticket',
                        'items_id' => $id,
                        'content'  => DemoData::RESOLUTIONS[random_int(0, count(DemoData::RESOLUTIONS) - 1)],
                    ]);
                } catch (\Throwable $e) {
                    // Resolution text is presentation. Losing it is not worth
                    // failing a seeding run that is otherwise fine.
                }
            }
            $made++;
        }

        $this->tally('tickets', $made);
        $o->writeln('  tickets with twelve months of history');
    }

    // ---------------------------------------------------------------- purge

    private function purge(OutputInterface $o): int
    {
        $o->writeln('');
        $o->writeln('  <comment>Removing seeded demonstration records</comment>');

        $m = DemoData::MARKER;
        $removed = 0;

        // Tickets carry no marker of their own, so they are matched by the
        // categories this seeder created. A ticket someone filed by hand
        // against one of those categories would go too, which is acceptable
        // only because this runs on demonstration instances.
        $catIds = [];
        foreach ($this->db->request([
            'SELECT' => 'id', 'FROM' => 'glpi_itilcategories',
            'WHERE'  => ['comment' => ['LIKE', '%' . $m . '%']],
        ]) as $row) {
            $catIds[] = (int) $row['id'];
        }

        if ($catIds) {
            foreach ($this->db->request([
                'SELECT' => 'id', 'FROM' => 'glpi_tickets',
                'WHERE'  => ['itilcategories_id' => $catIds],
            ]) as $row) {
                (new \Ticket())->delete(['id' => (int) $row['id']], true);
                $removed++;
            }
        }
        $o->writeln("  tickets removed: {$removed}");

        foreach ([
            \Computer::class     => 'glpi_computers',
            \ITILCategory::class => 'glpi_itilcategories',
            \User::class         => 'glpi_users',
            \Group::class        => 'glpi_groups',
            \Location::class     => 'glpi_locations',
        ] as $class => $table) {
            $n = 0;
            foreach ($this->db->request([
                'SELECT' => 'id', 'FROM' => $table,
                'WHERE'  => ['comment' => ['LIKE', '%' . $m . '%']],
            ]) as $row) {
                (new $class())->delete(['id' => (int) $row['id']], true);
                $n++;
            }
            $o->writeln(sprintf('  %-22s removed: %d', $table, $n));
        }

        $o->writeln('');
        $o->writeln('  <info>Purge complete.</info>');
        $o->writeln('');
        return self::SUCCESS;
    }
}
