export default function resolveMenu(clubhouseId) {
    return [
        'General',
        [
            {
                to: '/c-'+clubhouseId+'/home',
                icon: 'monitor-dashboard',
                label: 'Dashboard'
            },
            {
                to: '/c-'+clubhouseId+'/team-fight/dashboard',
                icon: 'account-group',
                label: 'Holdrunder'
            },
            {
                to: '/cancellations/redirect',
                icon: 'cancel',
                label: 'Afbud'
            },
            {
                to: '/c-'+clubhouseId+'/analytics',
                icon: 'google-analytics',
                label: 'Analytics'
            }
//        {
//            to: '/calendar',
//            icon: 'calendar',
//            label: 'Kalender'
//        }
        ],
        'Hjælp',
        [
            {
                to: '/about-us',
                label: 'Om os',
                icon: 'information'
            },
            {
                to: '/faq',
                label: 'FAQ',
                icon: 'frequently-asked-questions'
            },
        ],
        'Til spiller',
        [
            {
                to: '/c-'+clubhouseId+'/player',
                icon: 'badminton',
                iconRight: 'open-in-new',
                label: 'Spillerportal',
                target: '_blank'
            }
        ]
    ]
}
