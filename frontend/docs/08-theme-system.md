# Theme System Guidelines

We use CSS Custom Properties (variables) mapped inside Tailwind CSS v4.

---

## 1. System Preference
The `themeStore` defaults to `system` mode, matching the operating system display choice. Users can manually override theme modes to `light` or `dark`.

## 2. Design Tokens
Color styles use CSS variable maps:
```css
:root {
  --background: 0 0% 100%;
  --foreground: 222.2 47.4% 11.2%;
  --primary: 221.2 83.2% 53.3%;
  --primary-foreground: 210 40% 98%;
}

.dark {
  --background: 222.2 84% 4.9%;
  --foreground: 210 40% 98%;
  --primary: 217.2 91.2% 59.8%;
}
```
Avoid hardcoding raw hex values in portal styles. Use custom token utility classes instead.
