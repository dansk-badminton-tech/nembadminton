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
//        {
//            href: 'https://nembadminton.voteboards.com/',
//            label: 'Forslå ide',
//            icon: 'lightbulb',
//            target: '_blank'
//        },
        ]
//    'Examples',
//    [
//        {
//            to: '/tables',
//            label: 'Tables',
//            icon: 'table',
//            updateMark: true
//        },
//        {
//            to: '/forms',
//            label: 'Forms',
//            icon: 'square-edit-outline'
//        },
//        {
//            to: '/login',
//            label: 'Login',
//            icon: 'lock'
//        },
//        {
//            label: 'Submenus',
//            subLabel: 'Submenus Example',
//            icon: 'view-list',
//            menu: [
//                {
//                    href: '#void',
//                    label: 'Sub-item One'
//                },
//                {
//                    href: '#void',
//                    label: 'Sub-item Two'
//                }
//            ]
//        }
//    ],
    ]
}
