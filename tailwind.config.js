/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class', // Active le dark mode via class
  content: [
    "./**/*.{html,js,php}",
    "./views/**/*.{html,js,php}",
    "./components/**/*.{html,js,php}"
  ],
  theme: {
    extend: {
      colors: {
        // Light mode colors
        'white': '#ffffff',
        'light': '#f8fafc',
        'desaturate-fuscha': '#fae8ff',
        'desaturate-cyan': '#cffafe',
        
        // Dark mode colors - basées sur votre CSS
        'dark-white': '#393e46',
        'dark-light': '#222831',
        'dark-desaturate-fuscha': 'hsla(334, 8%, 67%, 0.15)',
        'dark-text-primary': '#eeeeee',
        'dark-text-secondary': '#eeeeee',
      },
      boxShadow: {
        'card': '8px 8px 32px rgba(250, 232, 255, 0.5)',
        'card-2': '8px 8px 32px rgba(207, 250, 254, 0.5)',
        'dark-card': '8px 8px 32px hsla(334, 8%, 67%, 0.15)',
        'dark-card-2': '8px 8px 32px rgba(207, 250, 254, 0.1)',
      },
      borderRadius: {
        '8': '8px',
        'rounded': '50%',
      },
    },
  },
  plugins: [],
}