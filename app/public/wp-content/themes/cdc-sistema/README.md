# CDC Sistema - Tema Custom

Sistema de gestión completo para Casa de la Cultura.

## Estado Actual

**🚧 En desarrollo - Fase 1**

Este tema es el frontend completo del sistema CDC. NO es un sitio web tradicional de WordPress.

## Estructura

```
cdc-sistema/
├── style.css              # Requerido por WordPress
├── functions.php          # Setup del tema
├── index.php              # Template principal (redirect)
├── screenshot.png         # Preview del tema
├── templates/             # Páginas del sistema (pendiente)
│   ├── login.php
│   ├── dashboard.php
│   ├── personas.php
│   └── ...
├── partials/              # Componentes reutilizables (pendiente)
│   ├── sidebar.php
│   ├── header.php
│   └── ...
├── includes/              # Lógica PHP
│   ├── setup.php
│   ├── auth.php
│   ├── permissions.php
│   ├── enqueue.php
│   └── ajax.php
└── assets/                # CSS, JS, imágenes
    ├── css/
    ├── js/
    └── images/
```

## Próximos Pasos (Fase 1)

1. Implementar sistema de login custom (DNI + contraseña)
2. Crear layout base con sidebar de navegación
3. Implementar dashboard/inicio con acciones rápidas
4. Crear sistema de enqueue para assets
5. Implementar permisos por rol

## Complementos

Este tema requiere el plugin **cdc-api** para funcionar (REST API backend).

## Uso

Para desarrollo local con Local by Flywheel:
1. Activar tema en Appearance > Themes
2. Activar plugin cdc-api
3. Navegar a http://localhost:10013/
