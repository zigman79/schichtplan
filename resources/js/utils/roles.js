export const arbeitszeitNoGender = [
    {
        value: 'user',
        label: 'Mitarbeiter',
    },
    {
        value: 'team-leader',
        label: 'Team-Leiter',
    },
    {
        value: 'administrator',
        label: 'Administrator',
    }
]


export const arbeitzeitGender = [
    {
        value: 'user',
        label: 'Mitarbeiter:in',
    },
    {
        value: 'team-leader',
        label: 'Team-Leiter:in',
    },
    {
        value: 'administrator',
        label: 'Administrator:in',
    }
]

export const arbeitszeit = (gender = false) => {
    return gender ? arbeitzeitGender : arbeitszeitNoGender
}
