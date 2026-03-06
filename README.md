# Hinnerks Map Tool for WordPress

A WordPress plugin that adds a Gutenberg block for displaying interactive maps from uploaded GeoJSON files — powered by [Leaflet](https://leafletjs.com/), [OpenStreetMap](https://www.openstreetmap.org/), and [OpenSeaMap](https://www.openseamap.org/).

---

## Features

- **Gutenberg block** — insert maps anywhere in posts and pages via the block editor
- **GeoJSON-driven** — upload any `.geojson` or `.json` file via the WordPress Media Library and attach it to the block
- **Interactive map** — OpenStreetMap base layer with an OpenSeaMap nautical overlay
- **Automatic bounds** — the map fits itself to the extent of the loaded GeoJSON data
- **Auto-generated legend** — labels and names from feature properties are read automatically; the legend is omitted when no meaningful labels are present
- **Hover tooltips** — hovering a feature shows its name, description, timestamps, start/finish points, and warnings (only fields that are present in the data)
- **Attachment metadata** — the file's WordPress **Title** and **Description** are displayed above the map; the **Caption** appears below it (all optional)
- **Multi-instance safe** — multiple map blocks on the same page each get a unique ID; Leaflet assets are only loaded once

---

## Requirements

| Requirement | Minimum version |
|---|---|
| WordPress | 6.5 |
| PHP | 7.4 |

No build step required. The editor script is plain JavaScript (IIFE) and requires no bundler.

---

## Installation

1. Copy the `hwmaptool` folder into `wp-content/plugins/`.
2. In the WordPress admin go to **Plugins → Installed Plugins** and activate **Hinnerks Map Tool for WordPress**.
3. The block is now available in the Gutenberg block inserter under the **Widgets** category.

---

## Usage

### Adding a map block

1. In the block editor, open the block inserter and search for **"Map based on GeoJSON"**.
2. Insert the block.
3. Click **Select GeoJSON file** to open the Media Library and upload or choose a `.geojson` / `.json` file.
4. The block preview confirms the selected file. Save / publish the post to render the map on the front end.

### Adding a title, subtitle, and caption

In the WordPress Media Library, select the GeoJSON file and fill in:

| Media Library field | Displayed as |
|---|---|
| **Title** | `<h6>` heading above the map |
| **Description** | Subtitle paragraph above the map (below the title) |
| **Caption** | Small `<figcaption>` below the map |

All three fields are optional and are hidden when empty.

### GeoJSON feature properties

The block reads these well-known properties automatically:

| Property key | Used for |
|---|---|
| `name` | Legend label and tooltip heading (lines, polygons) |
| `label` | Legend label and tooltip heading (points) |
| `title` | Fallback label for any geometry type |
| `description` | Tooltip body text |
| `start` / `finish` | Tooltip "A → B" route summary |
| `timestamp` | Tooltip date line (points) |
| `navigation_warning` | Tooltip warning line |
| `route_type` | Tooltip type annotation |

---

## File structure

```
hwmaptool/
├── hwmaptool.php               # Plugin bootstrap, block registration, MIME support
└── blocks/
    └── map-geojson/
        ├── block.json          # Block metadata (Gutenberg block API v3)
        ├── index.js            # Editor script (block registration, inspector controls)
        └── render.php          # Server-side render callback
```

---

## Third-party libraries

| Library | Version | License |
|---|---|---|
| [Leaflet](https://leafletjs.com/) | 1.9.4 | BSD-2-Clause |
| [OpenStreetMap](https://www.openstreetmap.org/copyright) tiles | — | ODbL |
| [OpenSeaMap](https://www.openseamap.org) tiles | — | CC-BY-SA |

Leaflet is loaded from the [unpkg](https://unpkg.com) CDN at runtime; no local copy is bundled.

---

## License

MIT — see [LICENSE](LICENSE) for the full text.

---

## Author

**Hinnerk Weiler**
