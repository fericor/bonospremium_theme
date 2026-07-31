# 📬 Formularios BonosPremium — Manual de instalación en nuevas webs

Este theme incluye **3 formularios listos** para enviar emails por SMTP de Brevo:

| Formulario | Plantilla | Campos |
|-----------|-----------|--------|
| 📩 Contacto | `template-contacto.php` | Nombre, Email, Teléfono, Mensaje |
| 🏪 Promociona tu negocio | `template-promociona.php` | Nombre, Email, Teléfono, Negocio, Web, Mensaje |
| 🎁 Recibir ofertas | `template-recibir-ofertas.php` | Nombre, Email, Ciudad |

---

## ✅ Qué hay que hacer en CADA web nueva (4 pasos)

### 1️⃣ Instalar el theme
Subir la carpeta del theme a `wp-content/themes/` y activarlo.

### 2️⃣ Configurar SMTP Brevo en `wp-config.php`

Añade estas líneas al final de tu `wp-config.php` (comilla tu propio wp-config). **Los valores dependen de la tienda**:

```php
// HOST y PUERTO son los mismos para todas las tiendas Brevo
if (!defined('BP_BREVO_HOST')) define('BP_BREVO_HOST', 'smtp-relay.brevo.com');
if (!defined('BP_BREVO_PORT')) define('BP_BREVO_PORT', 587);

// ⚠️ CAMBIA estos en cada tienda (login/email y password propios de Brevo)
define('BP_BREVO_USER', 'TU_LOGIN_BREVO@smtp-brevo.com');
define('BP_BREVO_PASS', 'TU_SMTP_KEY_BREVO');
define('BP_BREVO_FROM', 'info@TU-DOMINIO.com');   // remitente de esa tienda
```

> 💡 Si no hay credenciales definidas, el envío usa el `wp_mail()` normal de WordPress (sin SMTP). La web no se rompe, solo no se usa Brevo.

### 3️⃣ Configurar el email de destino (a quién llegan los mensajes)

Por defecto los 3 formularios envían a `info@bonospremium.com`. Para cambiar el destino en una tienda concreta, añade este código en `functions.php` de esa tienda (o un plugin snippet):

```php
add_filter('bp_form_contacto_to',   fn() => 'tudestino1@correo.com'); // contacto
add_filter('bp_form_promociona_to', fn() => 'tudestino2@correo.com'); // promociona
add_filter('bp_form_ofertas_to',    fn() => 'tudestino3@correo.com');  // ofertas
```

También puedes filtrar el asunto y el remitente:
```php
// Cambiar el subject de un formulario
add_filter('bp_forms_config', function($conf) {
    $conf['ofertas']['subject'] = '🎁 Suscripción ofertas - Mi tienda';
    return $conf;
});
```

### 4️⃣ Crear las páginas en WordPress

1. **Páginas → Añadir nueva**
2. Pon un título (ej. "Contacta con nosotros")
3. En el bloque "Atributos de página → Plantilla" elige la plantilla:
   - **Formulario Contacto**
   - **Formulario Promociona tu negocio**
   - **Formulario Recibir ofertas**
4. Publicar.

> Los slugs de URL no importan (los dicta el nombre de la página), pero se recomienda:
> - `/contacta-con-nosotros/`
> - `/promociona-tu-negocio/`
> - `/recibir-ofertas/`

---

## 🔍 Cómo comprobar que funciona

1. Rellena un formulario y envía.
2. Revisa que el destino recibe el correo.
3. Si no llega, revisa:
   - Que las credenciales Brevo en `wp-config.php` son correctas.
   - Que el dominio del `FROM` está verificado en Brevo.
   - Los logs de error (si hay plugin de logs de email, ver la respuesta).

---

## 📁 Archivos que forma parte del sistema

```
bonospremium_theme/
├── template-contacto.php         → Formulario Contacto
├── template-promociona.php       → Formulario Promociona
├── template-recibir-ofertas.php  → Formulario Recibir ofertas
├── functions.php                 → Config SMTP + handler de envío + filtros
└── assets/css/main.css           → Estilos (.bp-form, .bp-form-row, etc.)
```

> ⚠️ **Seguridad:** NUNCA guardar las SMTP keys (BP_BREVO_PASS) en el repo público. Siempre en `wp-config.php`.
