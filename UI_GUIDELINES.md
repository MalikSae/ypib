# UI Guidelines (PMB YPIB)

This document serves as the Single Source of Truth for UI components and design rules in this project. It ensures consistency and prevents repetitive inline styling by leveraging standard Blade components.

## Brand Colors
The following colors are configured in `tailwind.config.js`:
- **Primary**: `#0B41CB` (Blue)
- **Secondary**: `#F1B10E` (Yellow)
- **Neutral**: `#646E87` (Slate/Gray)
- **Success**: `#10B981` (Emerald)
- **Warning**: `#F59E0B` (Amber)
- **Error**: `#EF4444` (Red)

Use these via Tailwind classes (e.g., `text-primary-600`, `bg-secondary-500`, `border-neutral-200`).

---

## UI Components

All components are located in `resources/views/components/`. Use them with the `<x-...>` tag syntax.

### 1. Button (`<x-button>`)
A flexible button component with multiple variants, colors, and sizes.

**Props:**
- `type` (string): `button`, `submit`, `reset`. Default: `button`.
- `color` (string): `primary`, `secondary`, `success`, `danger`, `neutral`. Default: `primary`.
- `variant` (string): `solid`, `outline`, `ghost`. Default: `solid`.
- `size` (string): `sm`, `md`, `lg`. Default: `md`.

**Examples:**
```blade
<x-button color="primary">Simpan Data</x-button>
<x-button color="danger" variant="outline" size="sm">Hapus</x-button>
<x-button color="neutral" variant="ghost">Batal</x-button>
```

*(Note: Legacy Breeze buttons `<x-primary-button>`, `<x-secondary-button>`, `<x-danger-button>` still exist and wrap the new button logic under the hood).*

### 2. Card (`<x-card>`)
A standardized container with shadow, rounded corners, and border.

**Example:**
```blade
<x-card class="p-6">
    <h3 class="font-bold text-lg mb-4">Informasi Pendaftar</h3>
    <p>Konten berada di sini...</p>
</x-card>
```

### 3. Page Header (`<x-page-header>`)
Used at the top of a page to show the title, an optional description, and action buttons.

**Props:**
- `title` (string): The main heading text.
- `description` (string, optional): A subtitle text.

**Slots:**
- `actions`: Right-aligned area for buttons like "Tambah Baru".

**Example:**
```blade
<x-page-header 
    title="Data Pendaftar" 
    description="Kelola seluruh data calon mahasiswa baru di sini.">
    <x-slot name="actions">
        <x-button color="primary">Export Excel</x-button>
        <x-button color="success">Tambah Pendaftar</x-button>
    </x-slot>
</x-page-header>
```

### 4. Table System
A structured set of components to keep tables uniform.

**Components:**
- `<x-table>`: The main wrapper.
- `<x-table-heading>`: The `<th>` element.
- `<x-table-row>`: The `<tr>` element.
- `<x-table-cell>`: The `<td>` element.

**Example:**
```blade
<x-table>
    <thead>
        <tr>
            <x-table-heading>Nama</x-table-heading>
            <x-table-heading>Program Studi</x-table-heading>
            <x-table-heading>Status</x-table-heading>
            <x-table-heading>Aksi</x-table-heading>
        </tr>
    </thead>
    <tbody class="divide-y divide-neutral-200">
        <x-table-row>
            <x-table-cell class="font-medium text-neutral-900">Budi Santoso</x-table-cell>
            <x-table-cell>S1 Keperawatan</x-table-cell>
            <x-table-cell>
                <x-badge-status :active="true" active-text="Diterima" />
            </x-table-cell>
            <x-table-cell class="text-center">
                <div class="flex items-center justify-center gap-2">
                    <x-table-action-edit href="#" title="Edit Pendaftar" />
                    <x-table-action-delete action="#" text="Hapus data pendaftar ini?" />
                </div>
            </x-table-cell>
        </x-table-row>
    </tbody>
</x-table>
```
```

**Table Action Components (Single Source of Truth):**
To ensure absolute consistency across all tables, use the following specialized components inside `<x-table-cell>`:

- `<x-table-action-edit>`: Button for editing. Automatically uses outline variant and correct icon.
  - Prop `href` (string): Target URL.
  - Prop `title` (string): Tooltip text (default: 'Edit').
- `<x-table-action-delete>`: Button for deletion with built-in Alpine.js popup confirmation.
  - Prop `action` (string): Target form submit URL.
  - Prop `text` (string): Confirmation message.
- `<x-badge-status>`: Standardized Active/Inactive pill badge.
  - Prop `active` (boolean): Whether it's active.
  - Prop `toggleAction` (string, optional): If provided, wraps the badge in a clickable form for toggling status.

### 5. Form Components
Standardized inputs for consistent spacing, borders, and focus states.

**Components:**
- `<x-input-label>`: Used for labels. Supports `required` prop to show a red asterisk.
- `<x-text-input>`: The standard text input field. Supports `error` prop.
- `<x-textarea>`: Used for multiline text. Supports `error` prop.
- `<x-select>`: Used for dropdowns. Supports `error` prop.
- `<x-input-error>`: Used to display validation error messages.

**Example:**
```blade
<div class="mb-6">
    <x-input-label for="name" value="Nama Lengkap" required="true" />
    <x-text-input id="name" name="name" :value="old('name')" :error="$errors->has('name')" placeholder="Masukkan nama..." />
    <x-input-error :messages="$errors->get('name')" />
</div>

<div class="mb-6">
    <x-input-label for="status" value="Status" />
    <x-select id="status" name="status">
        <option value="aktif">Aktif</option>
        <option value="nonaktif">Nonaktif</option>
    </x-select>
</div>
```

---

## Best Practices

1. **Avoid Inline Styling**: Never use `style="..."` or write arbitrary CSS logic inside the views unless it's a very specific, isolated edge case. Use the utility classes provided by Tailwind.
2. **Reusability**: If you find yourself writing the same combination of Tailwind classes multiple times, consider creating a new Blade component or updating an existing one.
3. **Consistency**: Stick to the primary and secondary colors. Do not introduce new shades or hex codes directly in the templates.
