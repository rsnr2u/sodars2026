# SODAARS Frontend Workspace Folder Structure

This workspace is managed as a monorepo containing:

```
frontend/
├── apps/
│   └── admin/                  # Admin portal application
│       └── src/
│           ├── components/     # Custom local helper UI components
│           ├── layouts/        # Page layout wrapper frames
│           ├── pages/          # Custom module views
│           ├── routes/         # Router route files
│           ├── assets/         # Images, fonts, SVG icons
│           ├── styles/         # App-level styling variables
│           ├── hooks/          # React hooks
│           ├── lib/            # Library configure utilities
│           ├── mock/           # Local state data stores
│           └── types/          # TypeScript definitions
├── packages/
│   ├── ui/                     # Shared design system components
│   ├── mock-data/              # Faker.js Indian mock data generators
│   ├── theme/                  # Theme styling index.css configurations
│   └── shared/                 # Utilities and constants
├── docs/                       # Project documentation
└── public/                     # Static assets
```
