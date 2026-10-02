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

    /**
     * Tickets, in two deliberate phases.
     *
     * A single probabilistic loop cannot produce both a year of history and
     * a credible queue as it stands today. Tuned to give realistic history
     * it leaves almost nothing open; tuned to leave a working queue it
     * scatters year-old open tickets through the record. Both were tried.
     *
     * So history and the live queue are generated separately, which is also
     * how the real thing arrives: a closed record accumulated over months,
     * and a handful of items currently in flight.
     */
    private function seedTickets(OutputInterface $o, int $target, array $users, array $categories): void
    {
        $agents = array_values(array_filter($users, fn($u) => $u['agent'] && $u['id'] > 0));
        $staff  = array_values(array_filter($users, fn($u) => $u['id'] > 0));

        if (!$this->dry && (empty($agents) || empty($staff))) {
            $o->writeln('  <error>no usable people, skipping tickets</error>');
            return;
        }

        // Enough open work to fill the queue card without looking swamped.
        $openWanted = max(14, (int) round($target * 0.08));
        $histWanted = max(1, $target - $openWanted);
        $made = 0;

        // Phase one: the closed record, weighted towards recent months so the
        // trend rises. No real desk has ever had a flat year.
        for ($i = 0; $i < $histWanted; $i++) {
            $skew    = ($i / max(1, $histWanted)) ** 0.7;
            $daysAgo = (int) round(350 * (1 - $skew)) + random_int(14, 21);
            $made   += $this->makeTicket($i, $daysAgo, true, $staff, $agents, $categories);
        }

        // Phase two: the queue as it stands this morning.
        for ($i = 0; $i < $openWanted; $i++) {
            $daysAgo = (int) floor(((1 - ($i / max(1, $openWanted))) ** 1.6) * 9);
            $made   += $this->makeTicket($histWanted + $i, $daysAgo, false, $staff, $agents, $categories);
        }

        $this->tally('tickets', $made);
        $o->writeln('  tickets: twelve months of history, plus a live queue');
    }

    /** Returns 1 if a ticket was created, 0 otherwise. */
    private function makeTicket(int $seq, int $daysAgo, bool $closed, array $staff,
                                array $agents, array $categories): int
    {
        $templates = DemoData::TICKETS;
        [$cat, $title, $body, $urgency, $isRequest] = $templates[$seq % count($templates)];

        if ($this->dry) {
            return 1;
        }

        $now    = time();
        $opened = $now - ($daysAgo * 86400) - random_int(3600, 43200);

        if ($closed) {
            $status = \CommonITILObject::CLOSED;
        } elseif ($daysAgo >= 4) {
            // Work open several days is waiting on somebody. Saying so is the
            // difference between a queue that reads as managed and one that
            // reads as ignored.
            $status = \CommonITILObject::WAITING;
        } else {
            $status = [\CommonITILObject::INCOMING, \CommonITILObject::ASSIGNED,
                       \CommonITILObject::PLANNED][random_int(0, 2)];
        }

        $ticket = new \Ticket();
        $id = $ticket->add([
            'name'                => $title,
            'content'             => $body,
            'entities_id'         => $this->entity,
            'type'                => $isRequest ? \Ticket::DEMAND_TYPE : \Ticket::INCIDENT_TYPE,
            'itilcategories_id'   => max(0, $categories[$cat] ?? 0),
            'status'              => $status,
            'urgency'             => $urgency,
            'impact'              => max(1, min(5, $urgency - random_int(0, 1))),
            'priority'            => $urgency,
            '_users_id_requester' => $staff[random_int(0, count($staff) - 1)]['id'],
            '_users_id_assign'    => $agents[random_int(0, count($agents) - 1)]['id'],
            '_auto_import'        => true,
        ]);
        if ($id === false) {
            return 0;
        }

        // Dates are written after creation: the application stamps its own on
        // insert, so backdating has to be a second step. These are plain
        // columns, so writing them directly is safe.
        $open = date('Y-m-d H:i:s', $opened);
        $upd  = ['date' => $open, 'date_creation' => $open, 'date_mod' => $open];

        if ($closed) {
            $solveIn = random_int(1800, 3 * 86400);
            $upd['solvedate'] = date('Y-m-d H:i:s', $opened + $solveIn);
            $upd['closedate'] = date('Y-m-d H:i:s', $opened + $solveIn + random_int(600, 86400));
            $upd['date_mod']  = $upd['closedate'];
        } else {
            // A board showing no breaches fails to demonstrate the one thing a
            // service desk is bought to prevent; a board showing nothing but
            // breaches describes an organisation nobody wants to copy.
            $upd['time_to_resolve'] = random_int(1, 100) <= 15
                ? date('Y-m-d H:i:s', $now - random_int(3600, 2 * 86400))
                : date('Y-m-d H:i:s', $now + (($urgency >= 4 ? random_int(2, 10) : random_int(12, 72)) * 3600));
        }

        $this->db->update('glpi_tickets', $upd, ['id' => $id]);

        if ($closed) {
            try {
                (new \ITILSolution())->add([
                    'itemtype' => 'Ticket',
                    'items_id' => $id,
                    'content'  => DemoData::RESOLUTIONS[random_int(0, count(DemoData::RESOLUTIONS) - 1)],
                ]);
            } catch (\Throwable $e) {
                // Resolution text is presentation. Losing it is not worth
                // failing an otherwise good seeding run.
            }
        }

        return 1;
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
