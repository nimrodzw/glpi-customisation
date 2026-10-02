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
