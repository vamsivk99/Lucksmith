# LsProductComparisonSlider

## Projektübersicht
Das Plugin `LsProductComparisonSlider` erweitert Shopware 6 um ein innovatives CMS-Element, das eine interaktive Produktcomparison mit Slider-, Split-Screen- und Tabellenansicht bietet. Händler können 2 bis 5 Produkte mitsamt relevanter Attribute vergleichen und direkt in den Warenkorb legen.

## Entwicklungsgedanken

### Architektur-Entscheidungen
- Modulares CMS-Element mit separater Admin-, Resolver- und Storefront-Schicht.
- Services kapseln Normalisierung und Analytics für klare Verantwortlichkeiten.
- Twig-Komponenten strukturieren Slider, Karten und Vergleichstabelle.

### Herausforderungen & Lösungen
- Dynamische Datenauflösung → eigener Resolver mit Normalisierung und Highlight-Berechnung.
- Komplexe UI-Anforderungen → Storefront-Plugin mit State-Management, Touch- & Keyboard-Support.
- API-Integration → Store-API POST-Requests mit Debounce und Analytics-Hooks.

## AI-Tool Nutzung

### Verwendete Tools
- ChatGPT / Cursor: Unterstützung bei Boilerplate, Entwurf von Resolver- und JS-Strukturen.

### AI-generierter Code
- Geschätzter Anteil: 35%
- Hauptsächlich für: Boilerplate, Twig/JS Strukturen, Test-Skeletons.

## Zeitaufwand

### Gesamt: 18 Stunden
- Setup & Konfiguration: 2 h
- CMS-Element Basis: 3 h
- DataResolver: 4 h
- Frontend-Implementierung: 4 h
- API-Integration: 2 h
- Testing & Debugging: 2 h
- Dokumentation: 1 h

## Installation

### Voraussetzungen
- Shopware 6.6.10.x oder 6.7.x.x
- PHP ≥ 8.1
- Node.js ≥ 18

### Setup mit Dockware
1. `docker run -d --name shopware-dev -p 80:80 -p 443:443 -p 3306:3306 -p 8888:8888 -v ~/shopware-dev:/var/www/html/custom dockware/dev:6.6.10.1`
2. `docker exec -it shopware-dev bash`
3. `cd /var/www/html/custom/plugins`
4. Plugin-Verzeichnis klonen/kopieren
5. `bin/console plugin:refresh`
6. `bin/console plugin:install --activate LsProductComparisonSlider`
7. `bin/console cache:clear`

## Features

### Implemented ✅
- CMS-Element mit 2-5 Produktauswahl und Drag & Drop
- Highlight-Modi, Animationen, Farbvarianten, Tags und Recommendation-Badge
- Storefront-Slider mit Swipe, Split-Screen, Fullscreen und Share-Option
- Vergleichstabelle, Quick-Add-To-Cart, Wishlist-Integration, Analytics Hooks

### Nice-to-have (wenn Zeit war)
- Parallax-Effekte und erweiterte Animationsstile

### Known Limitations
- Analytics-Service aktuell mit Platzhaltern 
- Keine PDF/Excel-Export-Funktion umgesetzt

## Testing
- PHPUnit-Tests für Services und Resolver (Grundgerüst)
- Jest-Konfiguration vorbereitet
- Manuelle Tests in Shopware Storefront empfohlen

## Performance
- Lazy Loading für Produktbilder via `sw_thumbnails`
- Debouncte API-Calls im Frontend
- ResizeObserver für dynamische Layout-Anpassung

## Konfiguration
Im Shopware Admin lassen sich Titel, Produkte, Attribute, Farbschema, Highlight-Modus, Animationen, Tabelle, Quick-Add-to-Cart, Recommendation-Badge und Tags konfigurieren.

## Screenshots
- Admin-Ansicht (Platzhalter)
- Storefront Desktop (Platzhalter)
- Storefront Mobile (Platzhalter)

## Lizenz
MIT

