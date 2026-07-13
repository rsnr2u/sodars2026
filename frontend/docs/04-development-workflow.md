# SODAARS Development Workflow

This document details the workflow for building screens in the SODAARS UX-First workspace.

## Screen Building Checklist
1. **Analyze Requirements**: Align design specifications and information fields.
2. **Setup Mock Data**: Ensure standard Faker attributes are ready inside `@sodars/mock-data`.
3. **Establish Screen Route**: Create the route path and empty page template.
4. **Draft Layout**: Structure top header context, filters panel, listing grids, and action buttons.
5. **Add Components**: Integrate cards, buttons, badges, inputs, and form controls from the UI package.
6. **Interaction Flow**: Attach drawers, modals, density toggles, column managers, and filter updates.
7. **Accessibility & Shortcuts**: Connect keyboard listeners for rapid navigation (e.g. search focus, wizard triggers).
8. **Polishing**: Add skeletons, custom empty views, field validation errors.

Once approved by the user, the screen state is frozen, and business layers can be added in future phases.
