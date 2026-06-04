/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/views/**/*.php",
    "./index.php",
  ],
  theme: {
    extend: {
      colors: {
        // ─── Primary ─────────────────────────────────────────────
        primary:            '#091426',
        'primary-container': '#1E293B',
        'primary-fixed':    '#D8E3FB',
        'primary-fixed-dim':'#BCC7DE',
        'on-primary':       '#FFFFFF',
        'on-primary-container': '#8590A6',
        'on-primary-fixed': '#111C2D',
        'on-primary-fixed-variant': '#3C475A',
        'inverse-primary':  '#BCC7DE',

        // ─── Secondary (Income / Positive) ───────────────────────
        secondary:          '#006C49',
        'secondary-container': '#6CF8BB',
        'secondary-fixed':  '#6FFBBE',
        'secondary-fixed-dim': '#4EDEA3',
        'on-secondary':     '#FFFFFF',
        'on-secondary-container': '#00714D',
        'on-secondary-fixed': '#002113',
        'on-secondary-fixed-variant': '#005236',

        // ─── Tertiary (Expense / Negative) ───────────────────────
        tertiary:           '#330002',
        'tertiary-container': '#5A0008',
        'tertiary-fixed':   '#FFDAD7',
        'tertiary-fixed-dim': '#FFB3AD',
        'on-tertiary':      '#FFFFFF',
        'on-tertiary-container': '#FF5250',
        'on-tertiary-fixed': '#410004',
        'on-tertiary-fixed-variant': '#930013',

        // ─── Surface ─────────────────────────────────────────────
        background:         '#F7F9FB',
        surface:            '#F7F9FB',
        'surface-bright':   '#F7F9FB',
        'surface-dim':      '#D8DADC',
        'surface-container-lowest': '#FFFFFF',
        'surface-container-low':    '#F2F4F6',
        'surface-container':        '#ECEEF0',
        'surface-container-high':   '#E6E8EA',
        'surface-container-highest':'#E0E3E5',
        'surface-variant':  '#E0E3E5',
        'surface-tint':     '#545F73',
        'on-background':    '#191C1E',
        'on-surface':       '#191C1E',
        'on-surface-variant': '#45474C',
        'inverse-surface':  '#2D3133',
        'inverse-on-surface': '#EFF1F3',

        // ─── Outline ─────────────────────────────────────────────
        outline:            '#75777D',
        'outline-variant':  '#C5C6CD',

        // ─── Error ───────────────────────────────────────────────
        error:              '#BA1A1A',
        'error-container':  '#FFDAD6',
        'on-error':         '#FFFFFF',
        'on-error-container': '#93000A',
      },
      fontFamily: {
        headline: ['Phetsarath', 'sans-serif'],
        body:     ['Phetsarath', 'sans-serif'],
        label:    ['Phetsarath', 'sans-serif'],
      },
      fontSize: {
        'display-lg':  ['3.5rem',  { lineHeight: '1.1', letterSpacing: '-0.02em', fontWeight: '700' }],
        'headline-lg': ['2rem',    { lineHeight: '1.2', letterSpacing: '-0.01em', fontWeight: '700' }],
        'headline-sm': ['1.5rem',  { lineHeight: '1.3', letterSpacing: '-0.01em', fontWeight: '600' }],
        'title-lg':    ['1.375rem',{ lineHeight: '1.4', fontWeight: '600' }],
        'title-md':    ['1.125rem',{ lineHeight: '1.5', fontWeight: '600' }],
        'title-sm':    ['0.875rem',{ lineHeight: '1.5', fontWeight: '600' }],
        'body-md':     ['1rem',    { lineHeight: '1.6' }],
        'body-sm':     ['0.875rem',{ lineHeight: '1.5' }],
        'label-md':    ['0.75rem', { lineHeight: '1.4', letterSpacing: '0.05em', fontWeight: '500' }],
        'label-sm':    ['0.6875rem',{ lineHeight: '1.4', letterSpacing: '0.05em', fontWeight: '500' }],
        'label-xs':    ['0.625rem', { lineHeight: '1.4', letterSpacing: '0.06em', fontWeight: '500' }],
      },
      borderRadius: {
        'sm':  '2px',
        DEFAULT: '4px',
        'md':  '8px',
        'lg':  '12px',
        'xl':  '16px',
        '2xl': '24px',
        '3xl': '32px',
        'full': '9999px',
      },
      boxShadow: {
        'ambient':   '0 0 32px 0 rgba(9, 20, 38, 0.04)',
        'ambient-md':'0 12px 40px -12px rgba(9, 20, 38, 0.08)',
        'glass':     '0 4px 24px 0 rgba(9, 20, 38, 0.06)',
      },
      backdropBlur: {
        'glass': '20px',
      },
      spacing: {
        '18': '4.5rem',
        '22': '5.5rem',
      },
    },
  },
  plugins: [],
}
