/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./assets/**/*.js", "./templates/**/*.html.twig"],
  theme: {
    extend: {
      colors: {
        "custom-blue": "#42668B",
      },
    },
  },
  plugins: [],
};
