# Reusable Components Documentation

This directory contains reusable Twig components for the admin panel. These components ensure consistency across all pages.

## Components

### 1. Sidebar Component (`_sidebar.html.twig`)

The sidebar component provides navigation for the admin panel.

#### Required Parameters:

- **`roleLabel`** (string): The label displayed under the logo (e.g., "Admin Panel")
- **`activeMenu`** (string): The ID of the currently active menu item
- **`menuItems`** (array): Array of menu items with the following structure:
  - `id` (string): Unique identifier for the menu item
  - `route` (string): Symfony route name
  - `icon` (string): Material Symbols icon name
  - `label` (string): Display text for the menu item

#### Example Usage:

```twig
{% include 'components/_sidebar.html.twig' with {
    'roleLabel': 'Admin Panel',
    'activeMenu': 'dashboard',
    'menuItems': [
        {'id': 'dashboard', 'route': 'admin_dashboard', 'icon': 'dashboard', 'label': 'Tableau de bord'},
        {'id': 'students', 'route': 'admin_dashboard', 'icon': 'school', 'label': 'Dossiers Élèves'},
        {'id': 'teachers', 'route': 'admin_dashboard', 'icon': 'work', 'label': 'Enseignants'},
        {'id': 'classes', 'route': 'admin_classes', 'icon': 'domain', 'label': 'Classes'}
    ]
} %}
```

---

### 2. Navbar Component (`_navbar.html.twig`)

The navbar component provides the top navigation bar with search, notifications, and user profile.

#### Required Parameters:

- **`pageTitle`** (string): The title displayed in the navbar (e.g., "Tableau de bord")
- **`user`** (object): The current user object (typically `app.user`)

#### Example Usage:

```twig
{% include 'components/_navbar.html.twig' with {
    'pageTitle': 'Gestion des Classes',
    'user': app.user
} %}
```

---

## Standard Page Structure

All admin pages should follow this structure for consistency:

```twig
{% extends 'base.html.twig' %}

{% block title %}Page Title - Administration{% endblock %}

{% block body %}
<div class="flex h-screen w-full overflow-hidden">
    <!-- Sidebar Component -->
    {% include 'components/_sidebar.html.twig' with {
        'roleLabel': 'Admin Panel',
        'activeMenu': 'current_page_id',
        'menuItems': [
            {'id': 'dashboard', 'route': 'admin_dashboard', 'icon': 'dashboard', 'label': 'Tableau de bord'},
            {'id': 'students', 'route': 'admin_dashboard', 'icon': 'school', 'label': 'Dossiers Élèves'},
            {'id': 'teachers', 'route': 'admin_dashboard', 'icon': 'work', 'label': 'Enseignants'},
            {'id': 'classes', 'route': 'admin_classes', 'icon': 'domain', 'label': 'Classes'}
        ]
    } %}

    <!-- Main Content -->
    <main class="flex flex-1 flex-col overflow-hidden bg-background-light dark:bg-background-dark">
        <!-- Navbar Component -->
        {% include 'components/_navbar.html.twig' with {
            'pageTitle': 'Your Page Title',
            'user': app.user
        } %}

        <!-- Scrollable Page Content -->
        <div class="flex-1 overflow-y-auto p-4 lg:p-10">
            <div class="mx-auto flex max-w-[1400px] flex-col gap-8">
                <!-- Your page content goes here -->
            </div>
        </div>
    </main>
</div>
{% endblock %}
```

---

## Important Notes

1. **Always pass parameters**: Never include the components without the required parameters
2. **Keep menu items consistent**: Use the same menu items array across all pages
3. **Update activeMenu**: Set the `activeMenu` parameter to match the current page's ID
4. **Mobile responsive**: The sidebar automatically handles mobile responsiveness with a backdrop and toggle button
5. **Dark mode**: All components support dark mode out of the box

---

## Adding New Menu Items

When adding a new page to the admin panel:

1. Create a new route in your controller
2. Add the menu item to the `menuItems` array in all admin pages
3. Set the `activeMenu` parameter to the new item's ID on the new page

Example:
```twig
{'id': 'new_page', 'route': 'admin_new_page', 'icon': 'new_icon', 'label': 'New Page'}
```

---

## Material Symbols Icons

The components use Material Symbols Outlined icons. Find available icons at:
https://fonts.google.com/icons

Common icons used:
- `dashboard` - Dashboard/home
- `school` - Students/education
- `work` - Teachers/staff
- `domain` - Classes/buildings
- `settings` - Settings
- `logout` - Logout
