export default function resolveMenu(clubhouseId) {
    return [
        'Værktøjer',
        [
            {
                to: '/c-'+clubhouseId+'/player/calendar-generator',
                icon: 'calendar-sync-outline',
                label: 'Automatisk kalender'
            }
        ]
    ]
}
