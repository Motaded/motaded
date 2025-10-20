// tailwind v4 + vite + daisyui (ESM)
import daisyui from "daisyui";

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./components/**/*.twig",
    "./templates/**/*.html.twig",
    "./templates/**/*.html",
    "./templates/*.html.twig",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: "#F0F7F4",
          100: "#D9EDE3",
          200: "#B3DBC7",
          300: "#8DC9AB",
          400: "#47A679",
          500: "#008855", // Main DGA Green
          600: "#007A4D",
          700: "#006B44",
          800: "#005C3B",
          900: "#004D32",
        },
        secondary: {
          50: "#FDF3F1",
          100: "#FBE7E2",
          200: "#F5C3BA",
          300: "#F09F92",
          400: "#E6553F",
          500: "#DD2C00", // Accent Red
          600: "#C72600",
          700: "#B02000",
          800: "#991A00",
          900: "#821400",
        },
      },
    },
  },
  plugins: [daisyui],
  daisyui: {
    // Map DaisyUI tokens to your brand so btn-primary etc. match your palette
    themes: [
      {
        dga: {
          primary: "#008855", // used by btn-primary, text-primary, etc.
          secondary: "#DD2C00", // used by btn-secondary, text-secondary
          accent: "#47A679",
          neutral: "#3D4451",
          "base-100": "#FFFFFF",
        },
      },
    ],
  },
};
