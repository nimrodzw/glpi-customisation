<?php

/**
 * FrexCore demo dataset.
 *
 * Separated from the command that loads it so the content can be edited
 * without touching the loading logic, and so a prospect-specific variant is
 * a copy of this file rather than a fork of the seeder.
 *
 * DESIGN NOTES, because the choices here are deliberate:
 *
 * People carry names common across Southern and East Africa. Many are
 * biblical in origin because they genuinely are common there, not as a
 * theme imposed on the data.
 *
 * Servers are named after the twelve tribes. Naming a server estate after a
 * set is what real infrastructure teams actually do, so it reads as a house
 * convention rather than decoration, and it is the only place the theme
 * appears.
 *
 * Categories carry the regulation each one answers to. That is the point of
 * the dataset: an ITSM demo that shows tickets is unremarkable, while one
 * that shows a data subject access request with a statutory clock against it
 * speaks directly to whoever in the room owns compliance.
 *
 * @copyright Copyright (C) 2026 FrexCore and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace GlpiPlugin\Frexcore\Demo;

final class DemoData
{
    /**
     * Everything this seeder creates carries this marker, so the purge can
     * remove exactly what it made and nothing a person added by hand.
     */
    public const MARKER = '[frexcore-demo]';

    /** Branches. Real cities, so the map of the business is plausible. */
    public const LOCATIONS = [
        ['name' => 'Harare',        'country' => 'Zimbabwe'],
        ['name' => 'Bulawayo',      'country' => 'Zimbabwe'],
        ['name' => 'Johannesburg',  'country' => 'South Africa'],
        ['name' => 'Gaborone',      'country' => 'Botswana'],
        ['name' => 'Lusaka',        'country' => 'Zambia'],
        ['name' => 'Nairobi',       'country' => 'Kenya'],
        ['name' => 'Lagos',         'country' => 'Nigeria'],
        ['name' => 'Data centre',   'country' => 'South Africa'],
    ];

    public const DEPARTMENTS = [
        'Finance', 'Operations', 'Risk and Compliance', 'Underwriting',
        'Claims', 'Treasury', 'Human Resources', 'Information Technology',
        'Internal Audit', 'Customer Service',
    ];

    /**
     * Staff. first, last, department, and whether they work the service desk.
     */
    public const PEOPLE = [
        ['Tendai',   'Mukwena',    'Information Technology', true],
        ['Naomi',    'Chikafu',    'Information Technology', true],
        ['Elias',    'Banda',      'Information Technology', true],
        ['Precious', 'Dlamini',    'Information Technology', true],
        ['Amos',     'Kgosi',      'Risk and Compliance',    false],
        ['Ruth',     'Oyelaran',   'Risk and Compliance',    false],
        ['Blessing', 'Nyathi',     'Finance',                false],
        ['Thabo',    'Molefe',     'Finance',                false],
        ['Chipo',    'Marange',    'Claims',                 false],
        ['Esther',   'Wanjiru',    'Claims',                 false],
        ['Kudzai',   'Sibanda',    'Underwriting',           false],
        ['Nomsa',    'Khumalo',    'Underwriting',           false],
        ['Gift',     'Phiri',      'Operations',             false],
        ['Joseph',   'Adeyemi',    'Operations',             false],
        ['Lerato',   'Setshedi',   'Treasury',               false],
        ['Enoch',    'Mutasa',     'Internal Audit',         false],
        ['Grace',    'Achieng',    'Human Resources',        false],
        ['Simba',    'Zhou',       'Customer Service',       false],
        ['Rejoice',  'Moyo',       'Customer Service',       false],
        ['Daniel',   'Ncube',      'Treasury',               false],
    ];

    /**
     * Servers, named to a house convention. Purpose matters more than the
     * name: a prospect scanning this should recognise their own estate.
     */
    public const SERVERS = [
        ['REUBEN',    'Primary domain controller'],
        ['SIMEON',    'Secondary domain controller'],
        ['LEVI',      'File and print services'],
        ['JUDAH',     'Core banking application'],
        ['DAN',       'Database cluster, primary node'],
        ['NAPHTALI',  'Database cluster, replica'],
        ['GAD',       'Mail gateway'],
        ['ASHER',     'Backup and recovery'],
        ['ISSACHAR',  'Reporting and analytics'],
        ['ZEBULUN',   'Document management'],
        ['JOSEPH',    'Virtualisation host'],
        ['BENJAMIN',  'Jump host and privileged access'],
    ];

    /**
     * Categories, each carrying the obligation behind it.
     *
     * This is what separates the dataset from a generic one. A buyer whose
     * problem is proving compliance sees their own statutory language in the
     * ticket queue, with a clock attached.
     */
    public const CATEGORIES = [
        // Privacy and data protection
        ['Data subject access request',        'Privacy', 'GDPR Art. 15 / POPIA / Kenya DPA 2019'],
        ['Right to erasure request',           'Privacy', 'GDPR Art. 17 / Nigeria NDPA 2023'],
        ['Personal data breach',               'Privacy', 'GDPR Art. 33, notify within 72 hours'],
        ['Cross-border transfer review',       'Privacy', 'GDPR Ch. V / AU Malabo Convention'],
        ['Records of processing update',       'Privacy', 'GDPR Art. 30 / Zimbabwe CDPA 12:07'],
        ['Consent withdrawal',                 'Privacy', 'POPIA / Botswana DPA 2018'],
        ['Data protection impact assessment',  'Privacy', 'GDPR Art. 35'],

        // Security
        ['Suspected phishing',                 'Security', 'ISO 27001 A.5.24 incident management'],
        ['Malware detection',                  'Security', 'ISO 27001 A.8.7'],
        ['Privileged access review',           'Security', 'ISO 27001 A.5.18 / SOC 2 CC6.1'],
        ['Vulnerability remediation',          'Security', 'ISO 27001 A.8.8'],
        ['Lost or stolen device',              'Security', 'GDPR Art. 32 / POPIA s.19'],
        ['Card data environment change',       'Security', 'PCI DSS v4.0 Req. 6'],

        // Service desk
        ['Account and access',                 'Service',  'ITIL 4 request fulfilment'],
        ['Email and collaboration',            'Service',  'ITIL 4 incident management'],
        ['Core banking application',           'Service',  'ITIL 4 incident management'],
        ['Network and connectivity',           'Service',  'ITIL 4 incident management'],
        ['Hardware fault',                     'Service',  'ITIL 4 incident management'],
        ['Printing',                           'Service',  'ITIL 4 incident management'],
        ['New starter provisioning',           'Service',  'ITIL 4 request fulfilment'],
        ['Leaver offboarding',                 'Service',  'ISO 27001 A.6.5 / ITIL 4'],
        ['Change request',                     'Service',  'ITIL 4 change enablement'],
    ];

    /**
     * Ticket templates: [category, title, body, urgency 1-5, is_request].
     *
     * Written as things a person would actually type. Generic filler is the
     * tell that a demo is a demo, and a prospect reading three of these
     * decides whether the whole environment is real.
     */
    public const TICKETS = [
        ['Data subject access request',
         'Subject access request from former policyholder',
         'A former policyholder has asked for every record we hold on them, by email to the Harare branch. Statutory clock has started. Need the file from core banking, the claims history and any call recordings.', 4, true],

        ['Personal data breach',
         'Claims schedule emailed to the wrong broker',
         'A claims schedule with 140 policyholder names and ID numbers went to the wrong broker address. Recall attempted and failed. Needs breach assessment and a decision on notifying the regulator within 72 hours.', 5, false],

        ['Right to erasure request',
         'Erasure request, marketing contact list',
         'Customer has withdrawn consent and asked to be removed from all marketing. Need to confirm deletion across the CRM, the mailing platform and any backups still in retention.', 3, true],

        ['Cross-border transfer review',
         'Review transfer of payroll data to the regional HR platform',
         'Payroll for the Nairobi and Lagos branches is being moved to a platform hosted in the EU. Need a transfer assessment before the migration date.', 3, true],

        ['Records of processing update',
         'Processing register out of date after the new claims portal',
         'The claims portal went live last month and is not in the processing register. Needs a record created and a lawful basis recorded.', 2, true],

        ['Data protection impact assessment',
         'DPIA for the proposed customer analytics project',
         'Marketing want to profile customer behaviour across products. This is likely to need an impact assessment before any processing starts.', 3, true],

        ['Suspected phishing',
         'Payment instruction email appears to impersonate the CFO',
         'Treasury received an urgent payment instruction appearing to come from the CFO. Display name matches, sending domain does not. Payment held. Need headers analysed and the sender blocked group-wide.', 5, false],

        ['Lost or stolen device',
         'Laptop stolen from a vehicle in Johannesburg',
         'Underwriting laptop taken overnight. Device was encrypted. Need remote wipe confirmed and an assessment of whether any personal data was accessible.', 5, false],

        ['Privileged access review',
         'Quarterly privileged access review overdue',
         'The review of administrator accounts on the core banking platform is past due. Two accounts belong to staff who have since changed role.', 3, true],

        ['Vulnerability remediation',
         'Critical patch outstanding on the database replica',
         'Scanner has flagged a critical vulnerability on NAPHTALI. Needs a patch window agreed with Operations.', 4, false],

        ['Malware detection',
         'Endpoint protection quarantined a file on a branch workstation',
         'Detection on a Lusaka workstation. Machine isolated pending review. Need confirmation nothing moved laterally.', 4, false],

        ['Card data environment change',
         'Firewall rule change affecting the card environment',
         'Requested rule change touches the segment holding card data. Needs review and sign-off before the change window.', 3, true],

        ['Core banking application',
         'Core banking slow for the whole Harare branch',
         'Staff report transactions taking over a minute to post since this morning. Affecting the counter and the back office.', 4, false],

        ['Core banking application',
         'Policy document fails to generate for a specific product',
         'Generating documents for the commercial motor product returns an error. Other products are fine.', 3, false],

        ['Email and collaboration',
         'Mailbox full, cannot send',
         'Mailbox at quota and outbound mail is failing. Needs an increase or an archive policy applied.', 2, false],

        ['Email and collaboration',
         'Shared claims mailbox not receiving external mail',
         'Internal mail arrives, external does not. Brokers are phoning instead.', 4, false],

        ['Network and connectivity',
         'Branch link down in Bulawayo',
         'Whole branch offline. Failover to the backup link did not happen automatically.', 5, false],

        ['Network and connectivity',
         'Intermittent wireless drops on the second floor',
         'Users dropping off wireless several times an hour. Wired connections unaffected.', 2, false],

        ['Account and access',
         'Account locked after password change',
         'User cannot sign in after yesterday\'s password change. Locked out on both the laptop and the portal.', 3, false],

        ['Account and access',
         'Access needed to the underwriting reports folder',
         'New role requires access to the shared underwriting reports. Line manager has approved.', 2, true],

        ['New starter provisioning',
         'New starter in Claims, starts Monday',
         'Needs an account, laptop, mailbox, core banking access at claims handler level and a desk phone.', 3, true],

        ['Leaver offboarding',
         'Leaver in Treasury, last day Friday',
         'Needs all access revoked on the last day, mailbox delegated to the line manager and the laptop returned and wiped.', 4, true],

        ['Hardware fault',
         'Laptop will not charge',
         'Battery not charging and the machine only runs on mains. Replacement charger already tried.', 2, false],

        ['Hardware fault',
         'Branch server making an audible alarm',
         'Continuous alarm from the rack in Gaborone. Suspect a failed disk in the array.', 4, false],

        ['Printing',
         'Cannot print policy schedules at the counter',
         'Counter printer not responding. Customers are waiting while documents are emailed instead.', 3, false],

        ['Change request',
         'Add a disaster recovery test window for the core platform',
         'Internal Audit have asked for evidence of a recovery test. Need a window agreed and the test documented.', 3, true],

        ['Change request',
         'Increase storage on the document management server',
         'ZEBULUN is at 88 percent. Needs additional storage before the quarter end document load.', 2, true],
    ];

    /**
     * Name pools for the rest of the staff.
     *
     * Twenty named people cannot plausibly own ninety assets across eight
     * branches, and a prospect who counts is exactly the prospect worth
     * winning. These fill the gap so the headcount supports the estate.
     */
    public const MORE_FIRST = [
        'Tatenda', 'Nyasha', 'Farai', 'Rutendo', 'Tinashe', 'Anesu', 'Panashe',
        'Bongani', 'Sipho', 'Zanele', 'Mpho', 'Kagiso', 'Boitumelo', 'Oratile',
        'Wanjiku', 'Kamau', 'Njeri', 'Otieno', 'Chidi', 'Ngozi', 'Emeka',
        'Folake', 'Mutinta', 'Chanda', 'Lubasi',
    ];

    public const MORE_LAST = [
        'Mapfumo', 'Chirwa', 'Mudenda', 'Gumbo', 'Masuku', 'Ndlovu', 'Shumba',
        'Tshabalala', 'Mokoena', 'Radebe', 'Sithole', 'Mwale', 'Zulu',
        'Kariuki', 'Omondi', 'Mwangi', 'Okafor', 'Balogun', 'Adebayo',
        'Nkomo', 'Chigumba', 'Muchena', 'Simwanza', 'Kabwe', 'Lungu',
    ];

    // ============================================================
    // The estate
    //
    // A prospect clicks past the dashboard within a minute. What decides
    // the meeting is whether the next five screens hold up: whether assets
    // have owners, warranties and purchase dates; whether licences have
    // seat counts and renewal dates; whether contracts exist at all. Thin
    // data behind a good dashboard is worse than no demo, because it
    // teaches them the product is a shell.
    // ============================================================

    public const MANUFACTURERS = [
        'Dell', 'HP', 'Lenovo', 'Cisco', 'Fortinet', 'Canon', 'APC',
        'Microsoft', 'Veeam', 'Kaspersky', 'Ubiquiti', 'Yealink',
    ];

    public const STATES = [
        'In use', 'In stock', 'Under repair', 'Awaiting disposal', 'Retired',
    ];

    public const OPERATING_SYSTEMS = [
        'Windows 11 Pro', 'Windows 10 Pro', 'Windows Server 2022',
        'Ubuntu Server 22.04 LTS', 'Red Hat Enterprise Linux 9',
    ];

    /** [model, manufacturer, type] */
    public const COMPUTER_MODELS = [
        ['Latitude 5540',    'Dell',    'Laptop'],
        ['Latitude 7440',    'Dell',    'Laptop'],
        ['ThinkPad T14',     'Lenovo',  'Laptop'],
        ['EliteBook 840 G10','HP',      'Laptop'],
        ['OptiPlex 7010',    'Dell',    'Desktop'],
        ['ProDesk 400 G9',   'HP',      'Desktop'],
        ['PowerEdge R650',   'Dell',    'Server'],
        ['ProLiant DL380',   'HP',      'Server'],
    ];

    /** [model, manufacturer, is_colour, monthly_duty] */
    public const PRINTER_MODELS = [
        ['imageRUNNER C3226i', 'Canon', true,  8000],
        ['imageRUNNER 2630i',  'Canon', false, 6000],
        ['LaserJet M428fdw',   'HP',    false, 4000],
        ['Color LaserJet M480','HP',    true,  5000],
    ];

    public const MONITOR_MODELS = [
        ['P2422H',   'Dell'],
        ['U2723QE',  'Dell'],
        ['E24 G5',   'HP'],
    ];

    /** [model, manufacturer, role] */
    public const NETWORK_MODELS = [
        ['FortiGate 60F',       'Fortinet', 'Branch firewall'],
        ['FortiGate 200F',      'Fortinet', 'Core firewall'],
        ['Catalyst 9200-24P',   'Cisco',    'Access switch'],
        ['Catalyst 9300-48P',   'Cisco',    'Core switch'],
        ['UniFi U6-Pro',        'Ubiquiti', 'Wireless access point'],
    ];

    public const PHONE_MODELS = [
        ['T31P', 'Yealink'],
        ['T46U', 'Yealink'],
    ];

    /**
     * Software and its licensing position.
     *
     * [name, publisher, version, licence type, seats, months until renewal]
     *
     * Renewal months are deliberately mixed. Two fall inside the next
     * quarter, because a licence register where nothing is ever due proves
     * nothing, and the renewal a client had forgotten is usually the moment
     * the room goes quiet.
     */
    public const SOFTWARE = [
        ['Microsoft 365 Business Premium', 'Microsoft', '2026',    'Subscription', 120, 2],
        ['Windows Server Datacenter',      'Microsoft', '2022',    'Perpetual',     16, 0],
        ['Kaspersky Endpoint Security',    'Kaspersky', '12.4',    'Subscription',  95, 1],
        ['Veeam Backup and Replication',   'Veeam',     '12.1',    'Subscription',  16, 7],
        ['Adobe Acrobat Pro',              'Microsoft', '2026',    'Subscription',  25, 5],
        ['FortiGate UTM Support',          'Fortinet',  'FortiOS 7.4', 'Subscription', 8, 3],
        ['Core Banking Platform',          'Microsoft', '9.2',     'Perpetual',    150, 0],
        ['Sage Payroll',                   'Microsoft', '2026.1',  'Subscription',  12, 9],
    ];

    /** [name, what they supply] */
    public const SUPPLIERS = [
        ['Kopano IT Distributors',   'Hardware supply and warranty'],
        ['Sable Networks',           'Connectivity and managed links'],
        ['Mopane Technologies',      'Endpoint support and field services'],
        ['Highveld Cloud Services',  'Hosting and backup, data processor'],
        ['Chenai Secure Shredding',  'Certified media destruction'],
    ];

    /**
     * Contracts.
     *
     * [name, supplier, type, months until renewal, notice period in days]
     *
     * The data processing agreements are the point. Under GDPR Article 28,
     * POPIA and most African regimes a processor needs a written agreement,
     * and an auditor asking to see them is an entirely ordinary Tuesday.
     * Most buyers track them in a spreadsheet nobody has opened since the
     * day it was made.
     */
    public const CONTRACTS = [
        ['Hardware maintenance and warranty', 'Kopano IT Distributors',  'Maintenance',              8,  60],
        ['Primary and backup connectivity',   'Sable Networks',          'Service',                  4,  90],
        ['Endpoint support, all branches',    'Mopane Technologies',     'Support',                 11,  30],
        ['Hosting and backup services',       'Highveld Cloud Services', 'Data processing agreement', 2, 90],
        ['Certified media destruction',       'Chenai Secure Shredding', 'Data processing agreement', 6, 30],
    ];

    /**
     * Response and resolution targets, by priority.
     * [priority, name, respond within hours, resolve within hours]
     */
    public const SLA_TARGETS = [
        [5, 'Critical',  1,   4],
        [4, 'High',      2,   8],
        [3, 'Medium',    4,  24],
        [2, 'Low',       8,  48],
        [1, 'Very low', 24,  72],
    ];

    /**
     * Knowledge base.
     *
     * Half of these are service desk basics and half are the procedures a
     * regulator asks to see. An article titled "Personal data breach: the
     * first 24 hours" does more in a demo than any dashboard, because it
     * answers the question the compliance officer in the room came with.
     *
     * [title, category, body]
     */
    public const KB_ARTICLES = [
        ['Personal data breach: the first 24 hours', 'Privacy',
         "1. Contain. Isolate the affected system or account. Do not delete anything, it is evidence.\n"
         . "2. Record the clock. Note the time the breach was discovered. Under GDPR Article 33 the "
         . "notification window to the supervisory authority is 72 hours from awareness, not from the incident.\n"
         . "3. Raise a ticket under Privacy, Personal data breach. This starts the audit trail.\n"
         . "4. Assess. What categories of personal data, how many people, and is harm likely?\n"
         . "5. Decide on notification. The Data Protection Officer owns this decision, not IT.\n"
         . "6. Notify affected people where the risk to them is high.\n"
         . "7. Record the outcome even where you decide not to notify. The reasoning is part of the record."],

        ['Handling a data subject access request', 'Privacy',
         "A data subject may ask for a copy of everything held about them.\n\n"
         . "Log it the day it arrives under Privacy, Data subject access request. The statutory clock starts "
         . "on receipt, not on the day somebody notices the email.\n\n"
         . "Verify identity before disclosing anything. Releasing a file to an impostor is itself a breach.\n\n"
         . "Collect from every system, not only the obvious one: the core platform, email, the CRM, call "
         . "recordings and paper files.\n\n"
         . "Redact third parties. Another customer's details inside the file are not the requester's to receive."],

        ['Reporting a suspected phishing email', 'Security',
         "Do not click links or open attachments. Do not forward it to colleagues to ask what they think.\n\n"
         . "Use the report button in the mail client, or raise a ticket under Security, Suspected phishing "
         . "and attach the message as an attachment rather than pasting the text, so the headers survive.\n\n"
         . "If you already clicked: say so immediately. Nobody is in trouble for reporting quickly, and the "
         . "difference between an incident and a breach is usually how fast someone spoke up."],

        ['Lost or stolen device: what to do', 'Security',
         "Report it the same day, including out of hours, under Security, Lost or stolen device.\n\n"
         . "Tell us whether the device was encrypted and whether it held customer data. Both decide whether "
         . "this becomes a notifiable personal data breach.\n\n"
         . "We will remote wipe where possible and revoke the device's access tokens.\n\n"
         . "File a police report for stolen equipment. The reference is needed for insurance and for the "
         . "incident record."],

        ['Requesting access to a system or shared folder', 'Service',
         "Raise a ticket under Account and access with the system, the folder, and what you need to do.\n\n"
         . "Your line manager has to approve. Access is granted on the least needed to do the job, so ask "
         . "for the role you need rather than the one a colleague has.\n\n"
         . "Access to systems holding personal data carries a review date and will be revoked automatically "
         . "if it is not reconfirmed."],

        ['New starter: IT checklist', 'Service',
         "Raise the request at least five working days before the start date.\n\n"
         . "Tell us the role, the branch, the start date and the manager. The role decides the access "
         . "profile, so getting it right the first time avoids a second round of approvals.\n\n"
         . "Standard provision is an account, a mailbox, a laptop, core platform access at the level the "
         . "role requires, and a desk phone where the role takes customer calls.\n\n"
         . "Data protection induction is part of the first week and is tracked against this ticket."],

        ['Leaver: offboarding checklist', 'Service',
         "Notify IT as soon as a resignation is accepted, not on the last day.\n\n"
         . "On the final day all access is revoked, including remote access, the core platform and any "
         . "administrative accounts.\n\n"
         . "The mailbox is delegated to the line manager for a defined period and then archived to the "
         . "retention schedule.\n\n"
         . "Equipment is returned, wiped and either reissued or sent for certified destruction. The "
         . "destruction certificate is attached to the ticket, because an auditor will ask for it."],

        ['Password and multi-factor authentication', 'Security',
         "Use a long passphrase rather than a short complicated password. Length beats symbols.\n\n"
         . "Never reuse a work password anywhere else.\n\n"
         . "Multi-factor authentication is required for email, remote access and the core platform.\n\n"
         . "If you receive an authentication prompt you did not trigger, deny it and report it. An "
         . "unexpected prompt usually means somebody already has your password."],

        ['Printing a policy schedule at the counter', 'Service',
         "Select the counter printer for the branch rather than the default.\n\n"
         . "If the job does not appear, check the queue before resending. Repeated sends are the most "
         . "common cause of a jam while a customer is waiting.\n\n"
         . "Documents containing customer data must not be left on the printer. Use secure release where "
         . "the branch has it."],

        ['Requesting a change to a live system', 'Service',
         "Raise a change request with what is changing, why, when, and what happens if it goes wrong.\n\n"
         . "Changes touching the card data environment or any system holding personal data need review "
         . "before approval, not after.\n\n"
         . "Every change needs a back-out plan. A change nobody can reverse is an outage with a schedule."],
    ];

    /** [title, description, linked category] */
    public const PROBLEMS = [
        ['Core banking slows every weekday morning',
         'Repeated incidents between 08:00 and 09:30 across all branches. Suspected contention on the database '
         . 'replica during the overnight batch overrun. Workaround is to stagger branch opening reports.',
         'Core banking application'],
        ['Branch wireless drops on the second floor, Harare',
         'Multiple incidents over six weeks in the same area. Access point coverage appears insufficient since '
         . 'the floor was reorganised. Permanent fix requires an additional access point.',
         'Network and connectivity'],
        ['Shared mailboxes intermittently reject external mail',
         'Recurring across the claims and underwriting shared mailboxes. Correlates with sender reputation '
         . 'changes after the domain record update.',
         'Email and collaboration'],
    ];

    /** [title, description, linked category] */
    public const CHANGES = [
        ['Add a second wireless access point, Harare second floor',
         'Permanent fix for the recurring wireless problem. Requires a cabling run and a short outage on the '
         . 'floor switch outside business hours.',
         'Change request'],
        ['Move the overnight batch window',
         'Shift the batch to complete before 06:00 so it no longer overruns into branch opening. Needs sign off '
         . 'from Finance and Operations.',
         'Change request'],
        ['Increase storage on the document management server',
         'ZEBULUN is at 88 percent. Add storage before the quarter end document load.',
         'Change request'],
        ['Annual disaster recovery test for the core platform',
         'Recovery test to evidence the recovery time objective. Internal Audit have asked for the result.',
         'Change request'],
    ];

    // ============================================================
    // Depth
    //
    // The first pass gave every asset a name and an owner, which is where
    // most demo data stops. A server with no processor, no memory, no disk
    // and no address is a row in a list, and anyone who runs infrastructure
    // opens one, sees empty tabs, and stops believing the rest.
    // ============================================================

    /** Job titles. A Data Protection Officer in the directory is the one a buyer looks for. */
    public const USER_TITLES = [
        'Head of Information Technology', 'Systems Administrator', 'IT Support Analyst',
        'Network Engineer', 'Data Protection Officer', 'Compliance Officer',
        'Internal Auditor', 'Branch Manager', 'Underwriter', 'Senior Underwriter',
        'Claims Handler', 'Claims Supervisor', 'Finance Officer', 'Treasury Analyst',
        'Human Resources Officer', 'Customer Service Agent', 'Operations Supervisor',
    ];

    public const USER_CATEGORIES = ['Permanent', 'Fixed term', 'Contractor', 'Branch staff'];

    /**
     * Groups that can be assigned work, as distinct from departments.
     * [name, is this a technician group]
     */
    public const TECH_GROUPS = [
        ['Service Desk',            true],
        ['Infrastructure Team',     true],
        ['Network Team',            true],
        ['Applications Team',       true],
        ['Information Security',    true],
        ['Data Protection Office',  true],
    ];

    /** [processor name, cores, frequency MHz] */
    public const PROCESSORS = [
        ['Intel Xeon Silver 4310',  12, 2100],
        ['Intel Xeon Gold 6338',    32, 2000],
        ['AMD EPYC 7313',           16, 3000],
        ['Intel Core i7-1365U',     10, 1800],
        ['Intel Core i5-1335U',     10, 1300],
    ];

    /** [memory module, size MB, frequency] */
    public const MEMORY = [
        ['16GB DDR4-3200 SODIMM',  16384, '3200'],
        ['32GB DDR4-3200 SODIMM',  32768, '3200'],
        ['32GB DDR4-3200 ECC RDIMM', 32768, '3200'],
        ['64GB DDR4-3200 ECC RDIMM', 65536, '3200'],
    ];

    /** [disk, capacity MB, interface] */
    public const DISKS = [
        ['512GB NVMe SSD',   512000,  'NVMe'],
        ['1TB NVMe SSD',    1024000,  'NVMe'],
        ['2TB SAS 10K',     2048000,  'SAS'],
        ['4TB SAS 7.2K',    4096000,  'SAS'],
    ];

    /**
     * Addressing. One subnet per site, so network ports resolve to somewhere
     * sensible rather than to a scatter of unrelated addresses.
     * [site, subnet prefix, gateway]
     */
    public const SUBNETS = [
        'Harare'       => ['10.10.1',  '10.10.1.1'],
        'Bulawayo'     => ['10.20.1',  '10.20.1.1'],
        'Johannesburg' => ['10.30.1',  '10.30.1.1'],
        'Gaborone'     => ['10.40.1',  '10.40.1.1'],
        'Lusaka'       => ['10.50.1',  '10.50.1.1'],
        'Nairobi'      => ['10.60.1',  '10.60.1.1'],
        'Lagos'        => ['10.70.1',  '10.70.1.1'],
        'Data centre'  => ['10.0.1',   '10.0.1.1'],
    ];

    /**
     * Domains.
     *
     * [name, type, months until expiry, note]
     *
     * One expires inside the quarter on purpose. A lapsed domain takes down
     * mail and the customer portal together, and it is the failure every
     * operations manager in the room has either had or narrowly avoided.
     */
    public const DOMAINS = [
        ['shilohfinancial.com',  'Public',   2,  'Primary public domain, mail and website'],
        ['shilohgroup.co.zw',    'Public',   9,  'Zimbabwe trading domain'],
        ['shilohgroup.co.za',    'Public',  14,  'South Africa trading domain'],
        ['shiloh.local',         'Internal', 0,  'Internal Active Directory forest'],
        ['shilohpay.co.ke',      'Public',   5,  'Kenya payments portal'],
    ];

    /**
     * Policy documents.
     *
     * Short on purpose. These exist so the document register is not empty
     * and so contracts and incidents have something to point at, which is
     * what an auditor actually traces.
     *
     * [title, category, body]
     */
    public const DOCUMENTS = [
        ['Information Security Policy v4.2', 'Policy',
         "SHILOH FINANCIAL GROUP
INFORMATION SECURITY POLICY v4.2

"
         . "1. Purpose
To protect the confidentiality, integrity and availability of information held "
         . "by the group, and to meet obligations under ISO 27001 and applicable data protection law.

"
         . "2. Scope
All staff, contractors and third parties with access to group systems or data.

"
         . "3. Access control
Access is granted on least privilege and reviewed quarterly. Privileged "
         . "accounts require multi-factor authentication and separate credentials.

"
         . "4. Incident reporting
Suspected incidents are reported the same day through the service "
         . "desk. Suspected personal data breaches additionally start the notification assessment.

"
         . "5. Review
Reviewed annually by the Head of Information Technology and the Compliance "
         . "Officer, and after any significant incident.
"],

        ['Data Protection and Privacy Policy v2.1', 'Policy',
         "SHILOH FINANCIAL GROUP
DATA PROTECTION AND PRIVACY POLICY v2.1

"
         . "1. Principles
Personal data is processed lawfully, fairly and transparently, collected for "
         . "specified purposes, kept no longer than necessary and secured appropriately.

"
         . "2. Lawful basis
Every processing activity has a recorded lawful basis in the processing "
         . "register, which is maintained by the Data Protection Office.

"
         . "3. Data subject rights
Requests for access, correction, erasure, objection and portability "
         . "are logged through the service desk on the day of receipt so the statutory clock is "
         . "evidenced from the correct date.

"
         . "4. Breach notification
Assessment begins immediately on discovery. Where notification to a "
         . "supervisory authority is required it is made within 72 hours of awareness.

"
         . "5. Processors
No personal data is shared with a processor without a written agreement and "
         . "a transfer assessment where the processing leaves the country of origin.
"],

        ['Incident Response Plan v3.0', 'Procedure',
         "SHILOH FINANCIAL GROUP
INCIDENT RESPONSE PLAN v3.0

"
         . "Phase 1 Detect. Any member of staff may raise an incident. Information Security triages "
         . "within the response target for the assigned priority.

"
         . "Phase 2 Contain. Isolate affected systems and accounts. Preserve logs and images before "
         . "remediation; evidence destroyed during cleanup cannot be recovered.

"
         . "Phase 3 Assess. Determine whether personal data was involved. If so, the Data Protection "
         . "Officer owns the notification decision.

"
         . "Phase 4 Recover. Restore from known good backups and verify integrity before returning "
         . "systems to service.

"
         . "Phase 5 Review. A post incident review is held within ten working days and its actions are "
         . "tracked as changes.
"],
    ];

    /** Resolutions, so closed tickets do not all read the same. */
    public const RESOLUTIONS = [
        'Resolved and confirmed with the requester. No further action.',
        'Root cause identified and corrected. Monitoring for recurrence.',
        'Workaround applied and the underlying fault raised as a problem record.',
        'Completed within the agreed window. Evidence filed for audit.',
        'Access granted following line manager approval. Review date set.',
        'Assessed and closed. No notifiable impact identified.',
    ];
}
