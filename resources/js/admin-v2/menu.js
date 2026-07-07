export function adminMenu(clubhouseId) {
    return [
        'General',
        [
            {
                to: '/c-' + clubhouseId + '/home',
                icon: 'monitor-dashboard',
                label: 'Dashboard'
            },
            {
                to: '/c-' + clubhouseId + '/team-fight/dashboard',
                icon: 'account-group',
                label: 'Holdrunder'
            },
            {
                to: '/c-' + clubhouseId + '/teams',
                icon: 'shield-account',
                label: 'Hold'
            },
            {
                to: '/cancellations/redirect',
                icon: 'cancel',
                label: 'Afbud'
            },
            {
                to: '/c-' + clubhouseId + '/members',
                icon: 'account-multiple',
                label: 'Spillere'
            },
            {
                to: '/c-' + clubhouseId + '/analytics',
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
        ]
    ];
}

export function playerMenu(clubhouseId) {
    return [
        'Værktøjer',
        [
            {
                to: '/c-'+clubhouseId+'/home',
                icon: 'home',
                label: 'Dashboard'
            },
            {
                to: '/c-'+clubhouseId+'/player/calendar-generator',
                icon: 'calendar-sync-outline',
                label: 'Automatisk kalender'
            }
        ]
    ]
}

