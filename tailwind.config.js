/** @type {import('tailwindcss').Config} */
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Livewire/**/*.php',
    ],

    // Honor the dark sidebar by toggling the `.dark` class on <html> or wrapping element.
    darkMode: 'class',

    theme: {
        // ────────────────────────────────────────────────────────────────
        // Colors — Atelier KAYAN (Deep Navy + Antique Brass)
        // ────────────────────────────────────────────────────────────────
        colors: {
            transparent: 'transparent',
            current: 'currentColor',
            white: '#FFFFFF',
            black: '#000000',

            // Brand — Navy
            navy: {
                50:  '#EEF3F9',
                100: '#D8E2F0',
                200: '#B1C5E1',
                300: '#8AA8D2',
                400: '#638BC3',
                500: '#345882',
                600: '#244871',
                700: '#1B3A5C',
                800: '#142A45',
                900: '#0E1A2B',
                950: '#070D17',
            },

            // Brand — Brass (accent)
            brass: {
                50:  '#FAF6EF',
                100: '#F5EFE6',
                200: '#EBDDC6',
                300: '#DDC59C',
                400: '#C9A876',
                500: '#B08D5A',
                600: '#9A7746',
                700: '#7E5E38',
                800: '#5D4527',
                900: '#3F2F1A',
            },

            // Ink (text)
            ink: {
                50:  '#F8FAFC',
                100: '#F1F4F9',
                200: '#E7ECF3',
                300: '#DCE3EC',
                400: '#9AA5B1',
                500: '#7B8794',
                600: '#5B6878',
                700: '#3D4A5C',
                800: '#1E2A3A',
                900: '#0A1628',
            },

            // Surfaces
            surface: {
                DEFAULT: '#FFFFFF',
                2:       '#FAFBFC',
                3:       '#F8FAFC',
                elev:    '#FFFFFF',
                dark:    '#0E1A2B',
                dark2:   '#142234',
                dark3:   '#1B2D3F',
            },

            // Semantic
            success: {
                DEFAULT: '#047857',
                soft:    '#D1FAE5',
                50:      '#ECFDF5',
                100:     '#D1FAE5',
                500:     '#10B981',
                600:     '#047857',
                700:     '#065F46',
            },
            warning: {
                DEFAULT: '#B45309',
                soft:    '#FEF3C7',
                50:      '#FFFBEB',
                100:     '#FEF3C7',
                500:     '#F59E0B',
                600:     '#B45309',
                700:     '#92400E',
            },
            danger: {
                DEFAULT: '#B91C1C',
                soft:    '#FEE2E2',
                50:      '#FEF2F2',
                100:     '#FEE2E2',
                500:     '#EF4444',
                600:     '#B91C1C',
                700:     '#991B1B',
            },
            info: {
                DEFAULT: '#1E40AF',
                soft:    '#DBEAFE',
                50:      '#EFF6FF',
                100:     '#DBEAFE',
                500:     '#3B82F6',
                600:     '#1E40AF',
                700:     '#1E3A8A',
            },

            // Sidebar
            sb: {
                DEFAULT: '#0E1A2B',
                2:       '#142234',
                3:       '#1B2D3F',
                4:       '#243650',
                border:  '#1F2E42',
                ink:     '#E8EEF4',
                ink2:    '#A8B5C5',
                ink3:    '#748294',
                ink4:    '#4F5C70',
            },
        },

        // ────────────────────────────────────────────────────────────────
        // Typography
        // ────────────────────────────────────────────────────────────────
        fontFamily: {
            sans: ['Manrope', 'IBM Plex Sans Arabic', 'system-ui', 'sans-serif'],
            display: ['Fraunces', 'Reem Kufi', 'serif'],
            arabic: ['IBM Plex Sans Arabic', 'Manrope', 'sans-serif'],
            arabicDisplay: ['Reem Kufi', 'Fraunces', 'serif'],
            mono: ['JetBrains Mono', 'monospace'],
        },

        fontSize: {
            '2xs': ['0.6875rem', { lineHeight: '1rem' }],
            xs: ['0.75rem',   { lineHeight: '1rem' }],
            sm: ['0.8125rem', { lineHeight: '1.125rem' }],
            base: ['0.875rem',{ lineHeight: '1.375rem' }],
            lg: ['1rem',      { lineHeight: '1.5rem' }],
            xl: ['1.125rem',  { lineHeight: '1.625rem' }],
            '2xl': ['1.375rem',{ lineHeight: '1.875rem' }],
            '3xl': ['1.75rem', { lineHeight: '2.25rem' }],
            '4xl': ['2.25rem', { lineHeight: '2.75rem' }],
            '5xl': ['3rem',    { lineHeight: '3.5rem' }],
            '6xl': ['3.75rem', { lineHeight: '4rem' }],
        },

        // ────────────────────────────────────────────────────────────────
        // Spacing (4px base scale)
        // ────────────────────────────────────────────────────────────────
        extend: {
            spacing: {
                '4.5': '1.125rem',
                '5.5': '1.375rem',
                '13':  '3.25rem',
                '15':  '3.75rem',
                '18':  '4.5rem',
                '22':  '5.5rem',
                '30':  '7.5rem',
                '34':  '8.5rem',
                '38':  '9.5rem',
                '42':  '10.5rem',
                '46':  '11.5rem',
                '50':  '12.5rem',
                '62':  '15.5rem',
                '82':  '20.5rem',
            },

            // ────────────────────────────────────────────────────────────
            // Border radius
            // ────────────────────────────────────────────────────────────
            borderRadius: {
                'xs':  '6px',
                'sm':  '10px',
                DEFAULT: '14px',
                'md':  '14px',
                'lg':  '16px',
                'xl':  '20px',
                '2xl': '24px',
                '3xl': '28px',
            },

            // ────────────────────────────────────────────────────────────
            // Shadows
            // ────────────────────────────────────────────────────────────
            boxShadow: {
                '1': '0 1px 2px rgba(10, 22, 40, 0.04), 0 1px 1px rgba(10, 22, 40, 0.02)',
                '2': '0 2px 4px rgba(10, 22, 40, 0.05), 0 6px 16px rgba(10, 22, 40, 0.04)',
                '3': '0 4px 12px rgba(10, 22, 40, 0.06), 0 16px 32px rgba(10, 22, 40, 0.06)',
                '4': '0 10px 24px rgba(10, 22, 40, 0.08), 0 24px 56px rgba(10, 22, 40, 0.08)',
                '5': '0 20px 40px rgba(10, 22, 40, 0.12), 0 40px 80px rgba(10, 22, 40, 0.10)',
                'inner-1': 'inset 0 1px 0 0 rgba(255, 255, 255, 0.6)',
                'ring': '0 0 0 4px rgba(27, 58, 92, 0.08)',
                'ring-brass': '0 0 0 3px rgba(176, 141, 90, 0.18)',
                'glow': '0 0 0 1px rgba(255, 255, 255, 0.05), 0 8px 32px rgba(27, 58, 92, 0.20)',
            },

            // ────────────────────────────────────────────────────────────
            // Z-index scale
            // ────────────────────────────────────────────────────────────
            zIndex: {
                '60': '60',
                '70': '70',
                '80': '80',
                '90': '90',
                '100': '100',
            },

            // ────────────────────────────────────────────────────────────
            // Animations
            // ────────────────────────────────────────────────────────────
            animation: {
                'fade-in': 'fadeIn 200ms ease-out',
                'fade-out': 'fadeOut 200ms ease-out',
                'slide-up': 'slideUp 240ms cubic-bezier(0.16, 1, 0.3, 1)',
                'slide-down': 'slideDown 240ms cubic-bezier(0.16, 1, 0.3, 1)',
                'slide-in-right': 'slideInRight 240ms cubic-bezier(0.16, 1, 0.3, 1)',
                'slide-in-left': 'slideInLeft 240ms cubic-bezier(0.16, 1, 0.3, 1)',
                'scale-in': 'scaleIn 180ms cubic-bezier(0.16, 1, 0.3, 1)',
                'pulse-soft': 'pulseSoft 2.5s ease-in-out infinite',
                'spin-slow': 'spin 3s linear infinite',
                'shimmer': 'shimmer 2s infinite linear',
            },

            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                fadeOut: {
                    '0%': { opacity: '1' },
                    '100%': { opacity: '0' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(12px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideDown: {
                    '0%': { opacity: '0', transform: 'translateY(-12px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideInRight: {
                    '0%': { opacity: '0', transform: 'translateX(20px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                slideInLeft: {
                    '0%': { opacity: '0', transform: 'translateX(-20px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                scaleIn: {
                    '0%': { opacity: '0', transform: 'scale(0.96)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                pulseSoft: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.7' },
                },
                shimmer: {
                    '0%': { backgroundPosition: '-1000px 0' },
                    '100%': { backgroundPosition: '1000px 0' },
                },
            },

            // ────────────────────────────────────────────────────────────
            // Gradients
            // ────────────────────────────────────────────────────────────
            backgroundImage: {
                'gradient-navy': 'linear-gradient(135deg, #1B3A5C 0%, #0E1A2B 100%)',
                'gradient-navy-brass': 'linear-gradient(135deg, #1B3A5C 0%, #B08D5A 100%)',
                'gradient-success': 'linear-gradient(135deg, #047857 0%, #10B981 100%)',
                'gradient-warning': 'linear-gradient(135deg, #B45309 0%, #F59E0B 100%)',
                'gradient-danger': 'linear-gradient(135deg, #B91C1C 0%, #EF4444 100%)',
                'gradient-info': 'linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%)',
                'gradient-glow': 'radial-gradient(circle at 50% 0%, rgba(176, 141, 90, 0.15) 0%, transparent 60%)',
                'shimmer': 'linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%)',
            },

            // ────────────────────────────────────────────────────────────
            // Backdrop blur scale
            // ────────────────────────────────────────────────────────────
            backdropBlur: {
                'xs': '2px',
                'sm': '4px',
                'md': '8px',
                'lg': '12px',
                'xl': '16px',
                '2xl': '24px',
            },
        },
    },

    plugins: [
        forms({ strategy: 'class' }),
        typography,
    ],
};
