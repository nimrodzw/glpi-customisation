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
     * Ticket templates.
     *
     * [category, title, body, urgency 1-5, is_request, owning team, thread]
     *
     * Written as things a person would actually type. Generic filler is the
     * tell that a demo is a demo, and a prospect reading three of these
     * decides whether the whole environment is real.
     *
     * The thread is what happens after someone presses submit. A ticket with
     * a subject, an assignee and nothing else is a row in a table; what a
     * service desk is actually bought for is the exchange that follows, and
     * that is the screen a prospect opens second. Each beat is one entry on
     * the record:
     *
     *   ['u',  text]            the requester comes back with something
     *   ['t',  text]            the assigned technician replies, visible to all
     *   ['i',  text]            an internal note, not shown to the requester
     *   ['k',  text, minutes]   work logged against the ticket
     *   ['x',  team, text]      handed to another team, reassigned on the record
     *   ['j',  team, text]      a second team joins, the first stays on
     *   ['o',  text]            a manager is added as an observer
     *   ['s',  text]            the resolution, used when the ticket closes
     *
     * A kind ending in '?' is optional: it plays on roughly half of the
     * tickets built from the template, so two tickets with the same subject
     * do not read identically. Open tickets play only as far through the
     * thread as their age allows, which is what leaves a live queue holding
     * work at genuinely different stages.
     */
    public const TICKETS = [

        ['Data subject access request',
         'Subject access request from former policyholder',
         'A former policyholder has asked for every record we hold on them, by email to the Harare branch. Statutory clock has started. Need the file from core banking, the claims history and any call recordings.', 4, true,
         'Data Protection Office', [
            ['t', 'Logged and the statutory clock started from the date the request arrived at the branch rather than the date it reached this office. Confirming identity before anything is released.'],
            ['u', 'He has sent through a copy of his ID and the old policy number. Policy was cancelled in 2023 so it will be in the archive rather than live.'],
            ['k', 'Identity verified against the ID copy and the policy record. Extract pulled from core banking and from the claims system.', 75],
            ['i?', 'Call recordings are held by the contact centre platform for 24 months, so the 2023 calls are already outside retention. That is a legitimate answer, not a gap, but it needs saying in the response so it does not look like withholding.'],
            ['t', 'Extract is assembled. Third party names in the claims correspondence have been redacted, which the Act allows where disclosing them would identify someone who has not consented.'],
            ['u?', 'Understood on the redactions. Please send it to the address on the request and copy me.'],
            ['s', 'Response issued within the statutory period with the extract, the retention position on call recordings, and a note of the redactions and the basis for them. Evidence of the response filed against the request register.'],
         ]],

        ['Personal data breach',
         'Claims schedule emailed to the wrong broker',
         'A claims schedule with 140 policyholder names and ID numbers went to the wrong broker address. Recall attempted and failed. Needs breach assessment and a decision on notifying the regulator within 72 hours.', 5, false,
         'Information Security', [
            ['i', 'Containment first, assessment second. Mail trace requested to confirm the message was delivered and opened rather than only sent.'],
            ['t', 'Trace confirms a single delivery to one external recipient. No onward forwarding visible from our side. The receiving domain belongs to a broker we hold an agreement with, which changes the risk picture but not the obligation.'],
            ['x', 'Data Protection Office', 'Containment is done and the technical facts are established. Handing the assessment and the notification decision to the Data Protection Office, who own the 72 hour clock. Information Security stays available for the forensic side.'],
            ['o', 'Head of Compliance added as an observer. A breach that may be notifiable should not be the first thing they hear about after the decision has been taken.'],
            ['k', 'Severity assessed against the criteria: 140 data subjects, identity numbers included, single known recipient under contract, written deletion confirmation obtained.', 120],
            ['u?', 'The broker has replied in writing confirming the message was deleted and not opened by anyone else in their office.'],
            ['s', 'Assessed as not meeting the threshold for regulator notification, on the basis of a single identified recipient under a data processing agreement, written confirmation of deletion, and no evidence of onward disclosure. Recorded in the breach register with the reasoning, which is the part that matters if the regulator asks later. Sending controls reviewed as a separate change.'],
         ]],

        ['Right to erasure request',
         'Erasure request, marketing contact list',
         'Customer has withdrawn consent and asked to be removed from all marketing. Need to confirm deletion across the CRM, the mailing platform and any backups still in retention.', 3, true,
         'Data Protection Office', [
            ['t', 'Consent withdrawal recorded. Removing from the CRM marketing flags and the mailing platform. Note that erasure of marketing consent does not reach the policy record, which we are required to keep.'],
            ['k', 'Suppressed in the mailing platform and marketing flags cleared in the CRM.', 30],
            ['i?', 'Backups are the usual difficulty. They cycle out on a 35 day rotation and selective deletion inside a backup set is not practical, so the position recorded is suppression now and expiry by rotation, documented rather than claimed as immediate.'],
            ['t', 'Removed from all active marketing. The address is on the suppression list so a future import cannot quietly reinstate it.'],
            ['s', 'Marketing consent withdrawn and suppression applied across the CRM and the mailing platform. Retention of the underlying policy record explained to the customer with the statutory basis. Backup position documented.'],
         ]],

        ['Cross-border transfer review',
         'Review transfer of payroll data to the regional HR platform',
         'Payroll for the Nairobi and Lagos branches is being moved to a platform hosted in the EU. Need a transfer assessment before the migration date.', 3, true,
         'Data Protection Office', [
            ['t', 'Assessment started. Need the processor agreement and the hosting location in writing from the vendor before this can be signed off.'],
            ['u', 'Vendor has sent the agreement and confirmed the data stays in their Frankfurt region. HR want to go ahead on the 14th.'],
            ['k', 'Transfer assessment completed against the Kenyan and Nigerian requirements and the contractual safeguards reviewed.', 180],
            ['i?', 'The agreement is sound on security but silent on sub-processor notice, which is the clause that matters when they change hosting provider. Raised with Legal rather than blocking the migration over it.'],
            ['j', 'Information Security', 'Information Security asked to confirm the encryption and access controls claimed in the vendor response rather than taking them on the vendor word.'],
            ['s', 'Transfer approved subject to the signed agreement and a sub-processor notice clause added at renewal. Assessment filed and the processing register updated with the new recipient and location.'],
         ]],

        ['Records of processing update',
         'Processing register out of date after the new claims portal',
         'The claims portal went live last month and is not in the processing register. Needs a record created and a lawful basis recorded.', 2, true,
         'Data Protection Office', [
            ['t', 'Need the data categories the portal collects and the retention period the business intends, which the project did not document at go live.'],
            ['u', 'Collects name, contact details, policy number, claim detail and any supporting documents uploaded. Retention should follow the existing claims retention of seven years.'],
            ['k', 'Register entry created with categories, lawful basis, retention and the recipients list.', 45],
            ['s', 'Processing register updated with the claims portal, lawful basis recorded as contract performance, retention aligned to the existing claims schedule. Project checklist amended so a register entry is required before go live rather than after.'],
         ]],

        ['Data protection impact assessment',
         'DPIA for the proposed customer analytics project',
         'Marketing want to profile customer behaviour across products. This is likely to need an impact assessment before any processing starts.', 3, true,
         'Data Protection Office', [
            ['t', 'Screening confirms an assessment is required: profiling, large scale, and a product set that includes credit. Nothing should start until it is complete.'],
            ['u', 'Marketing are asking whether they can begin with anonymised data while the assessment runs.'],
            ['i', 'Worth being precise rather than obstructive. Genuinely anonymised data is outside scope, but what they have described is pseudonymised, which is not the same thing and is still personal data.'],
            ['t', 'They can start on aggregate figures that cannot be traced to an individual. The dataset as described is pseudonymised rather than anonymised, so it stays in scope until the assessment is signed.'],
            ['k', 'Assessment drafted with the risks, the mitigations and the residual risk position.', 240],
            ['o?', 'Head of Marketing added as an observer so the sign-off conditions are not relayed second hand.'],
            ['s', 'Assessment completed and approved with conditions: no credit product data in the first phase, a documented opt-out, and a review after six months. Conditions recorded against the project.'],
         ]],

        ['Suspected phishing',
         'Payment instruction email appears to impersonate the CFO',
         'Treasury received an urgent payment instruction appearing to come from the CFO. Display name matches, sending domain does not. Payment held. Need headers analysed and the sender blocked group-wide.', 5, false,
         'Information Security', [
            ['t', 'Payment held is the right call. Send the message as an attachment rather than a forward so the original headers survive.'],
            ['u', 'Attached. There were two others in the team who received the same thing this morning.'],
            ['k', 'Headers analysed. Sender domain registered four days ago, display name spoofed, reply-to pointing at a free mail address.', 40],
            ['i', 'Four days old is the detail that matters. This is targeted at us rather than bulk, which means a second attempt from a different domain is likely within the week.'],
            ['t', 'Sender domain and the reply-to address blocked at the gateway group-wide. A search across all mailboxes found seven further copies, all quarantined.'],
            ['j?', 'Service Desk', 'Service Desk asked to confirm with each of the seven recipients that nobody replied or opened the attachment, which the gateway cannot tell us.'],
            ['s', 'Sender blocked, copies quarantined, no recipient interacted with the message. Treasury payment verification procedure reconfirmed with the team: no payment instruction is actioned on email alone regardless of who appears to have sent it. Indicators shared with the sector group.'],
         ]],

        ['Lost or stolen device',
         'Laptop stolen from a vehicle in Johannesburg',
         'Underwriting laptop taken overnight. Device was encrypted. Need remote wipe confirmed and an assessment of whether any personal data was accessible.', 5, false,
         'Service Desk', [
            ['t', 'Device located in the asset register and a remote wipe issued. Account password reset and sessions revoked while we wait for the device to check in.'],
            ['x', 'Information Security', 'Passing to Information Security. The wipe is issued but whether data was reachable is their assessment, and this needs a view on the encryption state at the time it was taken rather than what the build should have had.'],
            ['k', 'Encryption state confirmed from the management console: full disk encryption active and compliant at the last check-in, which was the evening before the theft.', 50],
            ['j', 'Data Protection Office', 'Data Protection Office added. An encrypted device is very likely not a notifiable breach, but that is their determination to record rather than ours to assume.'],
            ['u?', 'Case number from the South African Police Service is attached for the insurance claim.'],
            ['i?', 'Wipe has not yet executed because the device has not come online. Worth stating plainly in the record: the wipe is pending rather than confirmed, and encryption is what is actually protecting the data.'],
            ['s', 'Device encrypted and compliant at the time of theft, account access revoked, remote wipe queued and confirmed when the device next checked in. Assessed as not notifiable on the basis of effective encryption, recorded with the evidence. Asset register updated and the insurance claim raised.'],
         ]],

        ['Privileged access review',
         'Quarterly privileged access review overdue',
         'The review of administrator accounts on the core banking platform is past due. Two accounts belong to staff who have since changed role.', 3, true,
         'Information Security', [
            ['t', 'Account list extracted and sent to the platform owner for attestation. The two flagged accounts are suspended pending their response rather than left active while we wait.'],
            ['u', 'Confirmed both have moved out of the team. Neither needs administrator access in their new roles.'],
            ['k', 'Both accounts removed from the administrator group and the change evidenced for audit.', 35],
            ['i?', 'The review was late because it depends on somebody remembering. Raising a change to put it on a schedule, since an access review that relies on memory will be late again next quarter.'],
            ['s', 'Review completed, two accounts removed, remaining accounts attested by the platform owner. Evidence filed for audit and a recurring task raised so the next review is scheduled rather than remembered.'],
         ]],

        ['Vulnerability remediation',
         'Critical patch outstanding on the database replica',
         'Scanner has flagged a critical vulnerability on NAPHTALI. Needs a patch window agreed with Operations.', 4, false,
         'Infrastructure Team', [
            ['t', 'Confirmed the finding is genuine rather than a scanner artefact. This is the replica, not the primary, so it can be patched without a service outage if we take it out of the read pool first.'],
            ['u?', 'Operations can give a window on Thursday evening after the batch run completes, usually by 21:00.'],
            ['k', 'Replica removed from the read pool, patched, rebooted and verified against the primary before being returned to service.', 95],
            ['i?', 'Replication lag was 40 minutes on return, cleared within the hour. Expected after a reboot but worth noting so the next person does not treat it as a fault.'],
            ['s', 'Patch applied in the agreed window with no service impact. Rescan confirms the finding cleared. The primary is scheduled for the same patch in the next change window.'],
         ]],

        ['Malware detection',
         'Endpoint protection quarantined a file on a branch workstation',
         'Detection on a Lusaka workstation. Machine isolated pending review. Need confirmation nothing moved laterally.', 4, false,
         'Information Security', [
            ['t', 'Machine is isolated. Reviewing the detection and the file origin before we decide whether this is a rebuild or a release.'],
            ['k', 'File traced to a document downloaded from a webmail session. Quarantined before execution. No child processes and no outbound connections from the host in the surrounding period.', 65],
            ['i', 'Caught before execution, so this is a detection working rather than an incident. The lateral movement question answers itself, but it still gets checked and written down rather than assumed.'],
            ['u?', 'The member of staff says they were expecting an invoice from a supplier and opened it without thinking.'],
            ['t', 'Nothing executed and nothing moved. Releasing the machine from isolation.'],
            ['s', 'Detection confirmed as pre-execution quarantine. No lateral movement, no persistence, no credential exposure. Workstation released. The branch is scheduled for a short refresher on attachments, which is the control that actually failed here.'],
         ]],

        ['Card data environment change',
         'Firewall rule change affecting the card environment',
         'Requested rule change touches the segment holding card data. Needs review and sign-off before the change window.', 3, true,
         'Network Team', [
            ['t', 'Rule reviewed. As written it opens a wider source range than the application needs, which would not survive the next assessment.'],
            ['x', 'Information Security', 'Anything touching the card segment needs Information Security sign-off before it goes in the change. Handing over with the narrowed rule proposed rather than the one requested.'],
            ['i?', 'The original request was almost certainly copied from an older rule. Worth saying so to the requester, because the same text will come back next quarter otherwise.'],
            ['u', 'The narrowed range works. We only need the two application hosts, not the whole subnet.'],
            ['k', 'Rule narrowed to the two hosts, reviewed against the segmentation requirements and documented with a business justification and a review date.', 55],
            ['s', 'Change approved as narrowed to two source hosts on a single port, with a documented justification and a twelve month review date. Implemented in the change window and segmentation testing confirmed unaffected.'],
         ]],

        ['Core banking application',
         'Core banking slow for the whole Harare branch',
         'Staff report transactions taking over a minute to post since this morning. Affecting the counter and the back office.', 4, false,
         'Service Desk', [
            ['t', 'Confirmed this is the branch rather than one workstation. Checking whether other branches are affected before this goes anywhere.'],
            ['i', 'Nairobi and Gaborone are posting normally, so this is Harare or the path to it rather than the platform.'],
            ['x', 'Applications Team', 'Handing to Applications. Branch-wide, single site, started this morning with no change to the workstations, so it is the application tier or the link rather than the desktop estate.'],
            ['j', 'Infrastructure Team', 'Infrastructure joining to look at the database side while Applications work the application tier. Two teams on it because the symptom does not yet say which layer it is.'],
            ['k', 'Traced to a long-running query holding locks on the posting table after an overnight job failed to complete and was not retried.', 110],
            ['o?', 'Branch Manager added as an observer so the counter has a status without having to ring the desk for it.'],
            ['u?', 'Posting times are back to normal at the counter as of about twenty minutes ago.'],
            ['s', 'Stalled overnight job identified as the cause, cleared, and posting times returned to normal. Monitoring added so the job failing to complete raises an alert rather than being noticed at the counter the following morning. Raised as a problem record to fix the retry behaviour.'],
         ]],

        ['Core banking application',
         'Policy document fails to generate for a specific product',
         'Schedules generate for every product except the group life cover. Error on screen and nothing in the output folder.', 3, false,
         'Applications Team', [
            ['t', 'Reproduced on a test policy, so this is the product configuration rather than anything the user did. Need the exact error text from the screen.'],
            ['u', 'Screenshot attached. It mentions a missing template reference.'],
            ['k', 'Template mapping for the group life product checked against the others. The mapping points at a template that was renamed during the last release and never repointed.', 70],
            ['i?', 'This was shipped broken and nobody noticed because group life is low volume. Worth a release checklist item rather than only a fix.'],
            ['t', 'Mapping corrected and a schedule generated successfully on test. Please try the live policy that failed and confirm.'],
            ['u?', 'Generated first time. Document looks correct.'],
            ['s', 'Template mapping corrected after a rename in the previous release left the group life product pointing at a template that no longer existed. Verified on the original policy. Release checklist updated to catch renamed templates.'],
         ]],

        ['Email and collaboration',
         'Mailbox full, cannot send',
         'Cannot send or receive. Message says the mailbox has reached its limit.', 2, false,
         'Service Desk', [
            ['t', 'Mailbox is at quota. A temporary increase is applied so sending works again while the mailbox is tidied rather than leaving you stuck.'],
            ['k', 'Temporary quota increase applied and archive policy checked against the retention schedule.', 20],
            ['u?', 'Sending works again. I have moved the old attachments into the archive.'],
            ['s', 'Quota restored and archiving explained. Mailbox is back under the standard limit with the temporary increase removed.'],
         ]],

        ['Email and collaboration',
         'Shared claims mailbox not receiving external mail',
         'Internal mail arrives in the shared claims mailbox but nothing from outside. Brokers say their messages are not bouncing.', 4, false,
         'Service Desk', [
            ['t', 'Internal working and external silently not arriving points at routing or filtering rather than permissions. Checking the gateway before the mailbox itself.'],
            ['x', 'Infrastructure Team', 'Handing to Infrastructure. The messages are reaching the gateway and being dropped there, which is outside what the desk can see or change.'],
            ['k', 'Transport rule found quarantining external mail to the shared address after a change to the anti-spoofing policy last week.', 60],
            ['i', 'The policy change was correct; the shared mailbox was simply not in the exception list. Nineteen messages are sitting in quarantine and need releasing rather than only fixing the rule going forward.'],
            ['t', 'Rule corrected and the nineteen held messages released to the mailbox. Brokers do not need to resend.'],
            ['s', 'Anti-spoofing policy exception added for the shared claims mailbox and the quarantined messages released. The other shared mailboxes were checked for the same gap and two more were corrected before anyone reported them.'],
         ]],

        ['Network connectivity',
         'Branch link down in Bulawayo',
         'Entire branch offline. No access to core banking or email. Staff are recording transactions on paper.', 5, false,
         'Network Team', [
            ['t', 'Link is down rather than degraded. Failover to the backup circuit initiated while the primary is investigated with the carrier.'],
            ['k', 'Backup circuit brought up and branch confirmed back on core banking. Carrier ticket raised against the primary.', 45],
            ['u', 'Counter is back online. Paper transactions from the last hour still need to be keyed in.'],
            ['o?', 'Branch Manager and Head of Operations added as observers. A branch recording transactions on paper is a business event rather than only a network one.'],
            ['i?', 'Carrier is reporting a fibre break on the route, so the primary will be hours rather than minutes. The branch is working on the backup, so this is no longer urgent even though it is not yet fixed.'],
            ['s', 'Branch restored on the backup circuit within the hour. Carrier confirmed a fibre break on the primary route and restored it the following day. Failover worked as designed, which is the point of paying for it. Manual transactions reconciled with Operations.'],
         ]],

        ['Network connectivity',
         'Intermittent wireless drops on the second floor',
         'Staff on the second floor lose wireless for short periods through the day. Wired connections are unaffected.', 2, false,
         'Network Team', [
            ['t', 'Wired unaffected narrows this to the wireless side. Pulling the controller logs for that floor across the last week before anyone moves hardware.'],
            ['k', 'Controller logs show repeated client disconnections on two access points, both on the same channel as a neighbouring tenant.', 50],
            ['i?', 'Interference rather than a fault, which is why swapping the access point would have changed nothing and cost a morning.'],
            ['t', 'Channel plan adjusted for the floor and transmit power reduced on the two access points to stop them overlapping.'],
            ['u?', 'Much better today. No drops reported since yesterday afternoon.'],
            ['s', 'Interference from a neighbouring tenant on an overlapping channel. Channel plan and power adjusted, drops stopped. A site survey is scheduled for the floor since the building has filled up since the wireless was designed.'],
         ]],

        ['Account and access',
         'Account locked after password change',
         'Changed my password this morning and now the account is locked. Cannot get into email or the banking platform.', 3, false,
         'Service Desk', [
            ['t', 'Account unlocked. The lockout came from a device still presenting the old password rather than from anything you typed at the desk.'],
            ['u', 'The phone was still signed in to mail. I have signed out and back in.'],
            ['k', 'Account unlocked and the lockout source confirmed from the authentication logs as the mobile device.', 15],
            ['s', 'Account unlocked and the stale session on the mobile device cleared. The pattern is common enough after a password change that it is now a step in the password change article rather than a ticket each time.'],
         ]],

        ['Account and access',
         'Access needed to the underwriting reports folder',
         'Moved into the underwriting team this month and cannot open the reports folder. Line manager has approved by email.', 2, true,
         'Service Desk', [
            ['t', 'Approval is on the ticket. Access is granted through the underwriting group rather than directly on the folder, so the permission comes away cleanly if the role changes again.'],
            ['k', 'Added to the underwriting reports group and the change recorded against the access register.', 20],
            ['i?', 'Direct folder permissions are how an estate ends up impossible to review. Group membership is marginally more work now and the difference between a two hour access review and a two day one later.'],
            ['u?', 'Folder opens now, thank you.'],
            ['s', 'Access granted through group membership with the manager approval attached to the record. Review date set to match the quarterly access review.'],
         ]],

        ['Onboarding',
         'New starter in Claims, starts Monday',
         'New claims assessor joins on Monday. Needs an account, a laptop, access to the claims system and a desk phone.', 2, true,
         'Service Desk', [
            ['t', 'Account created and the laptop is being built from the standard claims image. Need the line manager to confirm which claims role so the permissions match the job rather than copying another person.'],
            ['u', 'Claims assessor, same as the rest of the team. Reporting to the Claims Manager.'],
            ['k', 'Laptop built, encrypted, enrolled in management and tested. Account, mailbox, claims system access and desk extension configured.', 150],
            ['i?', 'Copying another person account is how access spreads. The role is now mapped to a group, so the next claims assessor is a membership rather than another guess.'],
            ['o?', 'Claims Manager added as an observer so they can see the build is ready before Monday rather than asking on the day.'],
            ['s', 'Account, laptop, claims access and desk phone ready and tested before the start date. Equipment signed for and recorded against the asset register with the owner set. Role mapped to a group so the next starter in the same role is a single step.'],
         ]],

        ['Offboarding',
         'Leaver in Treasury, last day Friday',
         'Treasury analyst leaving on Friday. Needs accounts disabled, laptop and token returned, and mailbox access handed to the manager.', 3, true,
         'Service Desk', [
            ['t', 'Scheduled for close of business Friday rather than done now, so nothing stops working mid-handover. Equipment return is the part that usually slips, so it is tracked on this ticket.'],
            ['j', 'Information Security', 'Information Security joining to revoke the privileged access and the payment platform token, which sit outside the standard leaver steps and are the ones that matter.'],
            ['k', 'Accounts disabled at close of business, sessions revoked, mailbox converted to shared and delegated to the manager for the retention period.', 60],
            ['u?', 'Laptop and token handed back to the branch on Friday afternoon.'],
            ['i?', 'Payment platform token confirmed revoked separately from the directory account. A disabled account with a live payment token is the gap nobody sees until an audit finds it.'],
            ['s', 'Accounts disabled on the last working day, privileged access and payment token revoked and confirmed separately, equipment returned and recorded, mailbox delegated to the manager with a retention end date set. Checklist evidence filed for audit.'],
         ]],

        ['Hardware fault',
         'Laptop will not charge',
         'Laptop stopped charging. Battery drains and the charging light does not come on. Working on a borrowed machine.', 2, false,
         'Service Desk', [
            ['t', 'Try the spare charger at the branch before we book a repair. A failed charger and a failed port look identical from the desk and one of them is a five minute fix.'],
            ['u', 'Spare charger does the same thing, so it is the laptop rather than the charger.'],
            ['k', 'Device checked, charging port confirmed faulty. Warranty status verified from the asset register and a repair raised with the supplier.', 30],
            ['i?', 'In warranty until next March, so this is a supplier repair rather than a replacement. The asset register having the purchase date is what made that a thirty second answer.'],
            ['s', 'Charging port repaired under warranty. Loan machine issued while the repair ran and returned on collection. Asset record updated with the repair.'],
         ]],

        ['Hardware fault',
         'Branch server making an audible alarm',
         'Continuous alarm from the rack in Gaborone. Suspect a failed disk in the array.', 4, false,
         'Infrastructure Team', [
            ['t', 'Management console confirms a single failed disk. The array is degraded rather than down, so the branch keeps working while this is replaced.'],
            ['i', 'Degraded is the important word for whoever reads this next. One more failure in the same array before the rebuild finishes is data loss, so the replacement is today rather than this week.'],
            ['k', 'Replacement disk dispatched to the branch and the failed disk identified by slot so the right one is pulled.', 25],
            ['u?', 'Disk swapped this morning. The alarm has stopped.'],
            ['k?', 'Array rebuild monitored to completion and the array confirmed healthy.', 40],
            ['s', 'Failed disk replaced and the array rebuilt to a healthy state. The remaining disks are the same age and batch, so a staged replacement is raised as a change rather than waiting for the next one to fail.'],
         ]],

        ['Printing',
         'Cannot print policy schedules at the counter',
         'Counter printer not responding. Customers are waiting while documents are emailed instead.', 3, false,
         'Service Desk', [
            ['t', 'Queue is stuck rather than the printer being offline. Clearing it now, and the counter should be printing within a few minutes.'],
            ['k', 'Print queue cleared and a test page confirmed from the counter workstation.', 15],
            ['u?', 'Printing again. Thank you for the quick turnaround.'],
            ['s', 'Stuck queue cleared and printing confirmed from the counter. A large document submitted twice was holding the queue; the driver has been updated to the version that handles the resubmission.'],
         ]],

        ['Change request',
         'Add a disaster recovery test window for the core platform',
         'Internal Audit have asked for evidence of a recovery test. Need a window agreed and the test documented.', 3, true,
         'Infrastructure Team', [
            ['t', 'A test that proves something has to be a restore into an isolated environment rather than a confirmation that backups ran. Proposing the second Saturday, which avoids both the month end and the batch run.'],
            ['u', 'Second Saturday works for Audit. They want to observe rather than only receive the report.'],
            ['o', 'Internal Audit added as observers, which is simpler than writing them a separate report and more convincing than one.'],
            ['k', 'Recovery test executed into an isolated environment. Restore completed and the recovery time measured against the stated objective.', 300],
            ['i?', 'Recovery came in at four hours ten against a four hour objective. Reporting the real figure rather than rounding it, since a test that always passes is not a test.'],
            ['s', 'Recovery test completed and documented with the measured recovery time, the one objective missed by ten minutes, and the two steps that caused it. Evidence provided to Internal Audit. The two steps are raised as their own changes rather than noted and forgotten.'],
         ]],

        ['Change request',
         'Increase storage on the document management server',
         'ZEBULUN is at 88 percent. Needs additional storage before the quarter end document load.', 2, true,
         'Infrastructure Team', [
            ['t', 'Growth rate checked rather than only the current figure. At the current rate it reaches 95 percent before quarter end, so this is worth doing now rather than at the deadline.'],
            ['k', 'Volume extended and the filesystem grown online with no outage.', 55],
            ['i?', 'Alert threshold was set at 90 percent, which on this growth rate leaves about a fortnight. Lowering it to 80 so the next one arrives with time to plan rather than time to react.'],
            ['s', 'Storage extended with no service interruption and the alert threshold lowered so the next increase is planned rather than urgent. Capacity now covers eighteen months at the current growth rate.'],
         ]],
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
