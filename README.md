# BWC Simple Img Header (WPBakery)

En-tête image (hero) + titres + **CTA dynamique** vers un spectacle.  
Sélection **en autocomplete** d’un post du CPT `class` (zéro mise à jour du plugin pour ajouter un spectacle).

Shortcode : `[bwc_simple_img_header]`.

---

## Sommaire
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Mises à jour via Git Updater](#mises-à-jour-via-git-updater)
- [Utilisation](#utilisation)
  - [Depuis WPBakery](#depuis-wpbakery)
  - [En shortcode](#en-shortcode)
  - [Attributs](#attributs)
- [Structure](#structure)
- [Notes techniques](#notes-techniques)
- [Dépannage](#dépannage)
- [Versioning](#versioning)
- [Changelog](#changelog)
- [Licence](#licence)

---

## Prérequis
- WordPress ≥ **6.0**
- PHP ≥ **8.0**
- **WPBakery Page Builder** ≥ 6.x (Visual Composer premium)
- Un Custom Post Type **`class`** avec meta `_wcs_timestamp` (Unix), `_wcs_reservation_link` (URL)

## Installation
1. Copier le dossier du plugin dans :  
   `wp-content/plugins/bwc-eventsschedulewp-imgheader/`
2. Activer l’extension dans **Extensions**.
3. Vérifier que **WPBakery** est actif.

## Mises à jour via Git Updater
Le plugin est compatible **Git Updater** (gratuit).

En-têtes du fichier principal :
```php
GitHub Plugin URI: beworldcorp/wpb-bwc-eventsschedulewp-imgheader
Primary Branch: main
