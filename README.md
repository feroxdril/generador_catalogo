# WFX Wholesale Catalog Generator

Plugin profesional de WordPress/WooCommerce para generar catálogos PDF de productos mayoristas.

![Version](https://img.shields.io/badge/version-1.1.1-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%2B-purple.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0-green.svg)

## 📋 Descripción

WFX Wholesale Catalog Generator es un plugin completo que permite a tiendas online crear catálogos PDF profesionales de productos mayoristas de forma rápida y sencilla. Ideal para distribuidores, mayoristas y negocios B2B que necesitan compartir catálogos actualizados con sus clientes.

## ✨ Características

### Interfaz de Usuario
- 🎯 **Selección Visual Intuitiva**: Interfaz moderna con checkboxes para seleccionar productos
- 🔍 **Búsqueda en Tiempo Real**: Encuentra productos instantáneamente mientras seleccionas
- 💰 **Precios Mayoristas Personalizados**: Define precios especiales por producto
- 📊 **Contador de Selección**: Visualiza cuántos productos has seleccionado
- 💾 **Guardar Selección**: Guarda tu selección para futuras ediciones

### Generación de PDF
- 🖼️ **Logo Personalizado**: Añade el logo de tu empresa
- 📝 **Título Configurable**: Personaliza el título del catálogo
- 🏷️ **Información Completa**: Imágenes, nombres, descripciones, SKU
- 💵 **Precios Destacados**: Precios mayoristas resaltados en verde
- 📦 **Control de Stock**: Muestra disponibilidad (opcional)
- 📞 **Datos de Contacto**: Email y teléfono en el footer
- 📄 **Numeración Automática**: Páginas numeradas automáticamente

### Configuración
- ⚙️ **Panel de Configuración Completo**: Todas las opciones en un solo lugar
- 🎨 **Media Uploader**: Sube tu logo fácilmente
- 🔧 **Opciones Flexibles**: Activa/desactiva SKU y stock según necesites
- 💱 **Símbolo de Moneda**: Personaliza el símbolo de tu moneda

### Meta Boxes de Producto
- ✅ **Checkbox de Inclusión**: Marca productos para catálogo mayorista
- 💵 **Precio Mayorista Individual**: Define precio por producto
- 🎯 **Integración con WooCommerce**: Se integra naturalmente en la edición de productos

## 📦 Requisitos

- WordPress 5.8 o superior
- WooCommerce 5.0 o superior
- PHP 7.4 o superior

## 🚀 Instalación

### Opción 1: Instalación desde ZIP

1. Descarga el plugin como archivo ZIP desde GitHub
2. Ve a **Plugins > Añadir nuevo > Subir plugin** en WordPress
3. Selecciona el archivo ZIP y haz clic en **Instalar ahora**
4. Activa el plugin

### Opción 2: Instalación Manual

```bash
# Clona el repositorio en tu directorio de plugins
cd wp-content/plugins/
git clone https://github.com/feroxdril/generador_catalogo.git wfx-wholesale-catalog
```

**Nota:** TCPDF está incluido en el plugin desde la versión 1.1.0

## 📖 Uso

### Configuración Inicial

1. Ve a **Catálogo PDF > Configuración** en el menú de WordPress
2. Completa los datos de tu empresa:
   - Nombre de la empresa
   - Logo (usa el botón para subir una imagen)
   - Título del catálogo
   - Email de contacto
   - Teléfono de contacto
3. Configura las opciones de visualización:
   - Mostrar SKU en el catálogo
   - Mostrar stock disponible
   - Símbolo de moneda
4. Haz clic en **Guardar Configuración**

### Generar un Catálogo

1. Ve a **Catálogo PDF** en el menú de WordPress
2. Usa el buscador para encontrar productos específicos
3. Selecciona los productos que deseas incluir (checkbox)
4. Opcionalmente, define precios mayoristas para cada producto
5. Haz clic en **Guardar Selección** para guardar tu trabajo
6. Haz clic en **Generar Catálogo PDF**
7. El PDF se generará y se abrirá automáticamente en una nueva ventana

### Marcar Productos en el Editor

1. Ve a **Productos** en WooCommerce
2. Edita cualquier producto
3. En el sidebar derecho verás el meta box **Catálogo Mayorista**
4. Marca **Incluir en catálogo mayorista**
5. Define el **Precio Mayorista**
6. Actualiza el producto

## 📁 Estructura del Proyecto

```
wfx-wholesale-catalog/
├── wfx-wholesale-catalog.php    # Archivo principal del plugin
├── composer.json                 # Dependencias de Composer
├── .gitignore                    # Archivos ignorados por Git
├── README.md                     # Este archivo
├── readme.txt                    # Readme de WordPress
├── includes/                     # Clases PHP del plugin
│   ├── class-wfx-admin.php      # Interfaz de administración
│   ├── class-wfx-pdf-generator.php  # Generador de PDF
│   └── class-wfx-product-meta.php   # Meta boxes de producto
├── assets/                       # Recursos frontend
│   ├── css/
│   │   └── admin.css            # Estilos de administración
│   └── js/
│       └── admin.js             # JavaScript de administración
├── lib/                          # Librerías externas
│   └── README.md                # Instrucciones para TCPDF
└── vendor/                       # Dependencias de Composer (no incluido)
```

## 🛠️ Desarrollo

### Tecnologías Utilizadas

- **Backend**: PHP 7.4+, WordPress API, WooCommerce API
- **Frontend**: jQuery, WordPress Media Uploader
- **PDF**: TCPDF 6.6+
- **Estilos**: CSS3 con Flexbox
- **Gestión de dependencias**: Composer

### Seguridad

El plugin implementa las mejores prácticas de seguridad:
- ✅ Nonces para todas las peticiones AJAX
- ✅ Verificación de permisos (`current_user_can`)
- ✅ Sanitización de datos de entrada (`sanitize_text_field`, `sanitize_email`, etc.)
- ✅ Escapado de datos de salida (`esc_html`, `esc_url`, `esc_attr`)
- ✅ Validación de tipos de archivo
- ✅ Prevención de acceso directo (`ABSPATH`)

### Estándares de Código

- Sigue los [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Comentarios en español para mejor comprensión
- Funciones documentadas con PHPDoc
- Código modular y reutilizable

## 🎨 Capturas de Pantalla

_(Las capturas se añadirán en futuras versiones)_

1. **Página Principal**: Interfaz de selección de productos
2. **Configuración**: Panel de opciones del plugin
3. **PDF Generado**: Ejemplo de catálogo PDF
4. **Meta Box**: Campos en productos individuales

## 🔄 Changelog

### Version 1.1.1 (2026-01-02)
🔧 **Correcciones Críticas:**
- Fixed: Corregida detección de TCPDF en `/lib/tcpdf/`
- Fixed: Ruta de carga de TCPDF con múltiples ubicaciones de fallback
- Fixed: Validación de disponibilidad de TCPDF antes de generar PDF
- Fixed: Mejor manejo de errores en generación de PDF
- Fixed: Validación de permisos de carpeta antes de escribir

🎨 **Mejoras:**
- Improved: Mensajes de error más descriptivos y útiles
- Improved: Logs de debug para troubleshooting
- Improved: Validación de imágenes antes de incluir en PDF
- Improved: Limpieza de texto mejorada para caracteres especiales
- Improved: Manejo de productos sin imagen
- Improved: Formateo de precios con separadores de miles

📖 **Documentación:**
- Added: Logs detallados para debugging
- Added: Comentarios en código para mantenimiento
- Updated: README con instrucciones de troubleshooting

### Version 1.1.0 (2025-12-29)
✅ Declarada compatibilidad con WooCommerce HPOS  
✅ TCPDF incluido en el plugin (no requiere instalación manual)  
✅ Mejorado el manejo de errores  
✅ Auto-guardado de precios mayoristas  
✅ Optimización general del código  
🐛 Corregidos problemas de compatibilidad

### Versión 1.0.0 (2024-12-29)

- ✨ Lanzamiento inicial
- ✨ Selección visual de productos con checkboxes
- ✨ Generación de PDF con TCPDF
- ✨ Precios mayoristas personalizados
- ✨ Búsqueda en tiempo real
- ✨ Configuración completa con media uploader
- ✨ Meta boxes en productos
- ✨ Interfaz responsive y moderna
- ✨ Sistema de guardado de selección
- ✨ AJAX para todas las operaciones
- ✨ Soporte para Composer y instalación manual de TCPDF

## 🤝 Contribuir

Las contribuciones son bienvenidas. Para contribuir:

1. Fork el proyecto
2. Crea una rama para tu característica (`git checkout -b feature/NuevaCaracteristica`)
3. Commit tus cambios (`git commit -m 'Añade nueva característica'`)
4. Push a la rama (`git push origin feature/NuevaCaracteristica`)
5. Abre un Pull Request

### Ideas para Futuras Características

- [ ] Soporte para productos variables
- [ ] Múltiples plantillas de PDF
- [ ] Categorías en el PDF
- [ ] Exportar a Excel/CSV
- [ ] Programar generación automática
- [ ] Envío automático por email
- [ ] Múltiples idiomas
- [ ] Campos personalizados en el PDF

## 📞 Soporte

Para soporte técnico, preguntas o sugerencias:

- 🌐 Web: [https://www.wifextelematics.com](https://www.wifextelematics.com)
- 📧 Email: Configurable en el plugin
- 🐛 Issues: [GitHub Issues](https://github.com/feroxdril/generador_catalogo/issues)

## 📄 Licencia

Este plugin es software libre distribuido bajo la licencia GPL v2 o posterior.

```
WFX Wholesale Catalog Generator
Copyright (C) 2024 WFX Telematics

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
```

## 👥 Créditos

**Desarrollado por**: WFX Telematics  
**Autor**: WFX Telematics  
**Versión**: 1.1.1  
**Última actualización**: 2026-01-02

## 🙏 Agradecimientos

- Equipo de WordPress por su excelente CMS
- Equipo de WooCommerce por su potente plataforma de ecommerce
- Tecnick.com por la librería TCPDF
- Comunidad de código abierto

---

**⭐ Si este plugin te resulta útil, considera darle una estrella en GitHub!**