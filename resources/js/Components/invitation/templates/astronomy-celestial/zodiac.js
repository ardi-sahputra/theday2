// Zodiac sign lookup. Local helper — NOT an extension of useInvitationTemplate.
// DOB fields are NOT in schema (see spec section 16.1), so this helper is provided
// only for completeness. Real values come from `config.ac_groom_zodiac` and
// `config.ac_bride_zodiac` (user-set via customize wizard).

export const ZODIAC_SIGNS = [
    'aries','taurus','gemini','cancer','leo','virgo',
    'libra','scorpio','sagittarius','capricorn','aquarius','pisces',
]

export const ZODIAC_LABEL = {
    aries:        { en: 'Aries',        id: 'Aries',         range: '21 Mar – 19 Apr' },
    taurus:       { en: 'Taurus',       id: 'Taurus',        range: '20 Apr – 20 May' },
    gemini:       { en: 'Gemini',       id: 'Gemini',        range: '21 May – 20 Jun' },
    cancer:       { en: 'Cancer',       id: 'Cancer',        range: '21 Jun – 22 Jul' },
    leo:          { en: 'Leo',          id: 'Leo',           range: '23 Jul – 22 Aug' },
    virgo:        { en: 'Virgo',        id: 'Virgo',         range: '23 Aug – 22 Sep' },
    libra:        { en: 'Libra',        id: 'Libra',         range: '23 Sep – 22 Oct' },
    scorpio:      { en: 'Scorpio',      id: 'Scorpio',       range: '23 Oct – 21 Nov' },
    sagittarius:  { en: 'Sagittarius',  id: 'Sagitarius',    range: '22 Nov – 21 Dec' },
    capricorn:    { en: 'Capricorn',    id: 'Capricorn',     range: '22 Dec – 19 Jan' },
    aquarius:     { en: 'Aquarius',     id: 'Aquarius',      range: '20 Jan – 18 Feb' },
    pisces:       { en: 'Pisces',       id: 'Pisces',        range: '19 Feb – 20 Mar' },
}

// Pure helper. Optional — only useful if DOB ever lands in schema.
export function getZodiac(isoDate) {
    if (!isoDate) return null
    const d = new Date(isoDate)
    if (Number.isNaN(d.getTime())) return null
    const m = d.getMonth() + 1
    const day = d.getDate()
    if ((m === 3 && day >= 21) || (m === 4 && day <= 19))  return 'aries'
    if ((m === 4 && day >= 20) || (m === 5 && day <= 20))  return 'taurus'
    if ((m === 5 && day >= 21) || (m === 6 && day <= 20))  return 'gemini'
    if ((m === 6 && day >= 21) || (m === 7 && day <= 22))  return 'cancer'
    if ((m === 7 && day >= 23) || (m === 8 && day <= 22))  return 'leo'
    if ((m === 8 && day >= 23) || (m === 9 && day <= 22))  return 'virgo'
    if ((m === 9 && day >= 23) || (m === 10 && day <= 22)) return 'libra'
    if ((m === 10 && day >= 23) || (m === 11 && day <= 21))return 'scorpio'
    if ((m === 11 && day >= 22) || (m === 12 && day <= 21))return 'sagittarius'
    if ((m === 12 && day >= 22) || (m === 1 && day <= 19)) return 'capricorn'
    if ((m === 1 && day >= 20) || (m === 2 && day <= 18))  return 'aquarius'
    return 'pisces'
}
