# **DeTuristaAndo**

> **Descubre experiencias locales. Visita negocios a tu manera.**

**DeTuristaAndo** convierte negocios dispersos en experiencias fáciles de descubrir y recorrer. Un organizador publica una propuesta común, el visitante guarda una tarjeta en Google Wallet y cada negocio confirma las visitas. No existe un orden obligatorio: el beneficio se habilita al conocer `K` negocios distintos entre los `N` participantes.

La plataforma no es un directorio general, una aplicación de puntos ni un sistema de pagos. Su propósito es dar contexto a la oferta local y generar descubrimiento y tráfico cruzado entre negocios independientes.

> **Estado actual:** documentación previa al desarrollo de MVP01. La aplicación y sus integraciones todavía no están implementadas.

## El problema

Descubrir lugares interesantes todavía exige combinar mapas, redes sociales, publicaciones temporales y recomendaciones. El visitante debe investigar, comparar y construir su propio plan. Los negocios con poca presencia digital quedan fuera de ese proceso.

Una feria, asociación o grupo de comercios puede resolver parte del problema mediante promoción conjunta. Sin embargo, suele carecer de una herramienta sencilla para:

- Presentar la propuesta completa en un solo lugar.
- Mostrar qué negocios participan y dónde están.
- Permitir que cada persona elija su propio recorrido.
- Registrar visitas sin conectar cajas, inventarios o ventas.
- Reconocer a quien conoce varios participantes.
- Medir si las visitas se distribuyen entre negocios.

## La propuesta

Una **experiencia** reúne negocios coordinados previamente alrededor de una feria, evento o temática. El organizador define los participantes, las fechas, la meta `K/N`, el beneficio y sus condiciones. Cada visitante decide qué lugares conocer y en qué orden.

### Ejemplo completo

Una semana gastronómica reúne diez restaurantes y ofrece un beneficio al visitar cuatro de ellos. Una persona descubre la experiencia en `deturistaando.com` o mediante un QR expuesto por un participante. La landing muestra la propuesta, los negocios, el mapa, la meta y el beneficio.

La persona activa una participación anónima y guarda su tarjeta en Google Wallet. En cada restaurante presenta el QR privado de la tarjeta. El negocio lo escanea, revisa la operación y confirma la visita. Al completar cuatro negocios distintos, el sistema habilita el beneficio para su canje en los puntos autorizados.

Los participantes no comparten ventas, inventarios ni sistemas internos. Solamente operan la validación necesaria para la experiencia.

## Valor por actor

| Actor | Valor principal |
|---|---|
| Visitante | Recibe una propuesta concreta, visual y libre de recorrer sin instalar otra aplicación ni crear una cuenta. |
| Negocio participante | Obtiene visibilidad y visitas cruzadas mediante una operación limitada que no requiere integración técnica. |
| Organizador | Publica y opera la experiencia desde un único lugar, con progreso, beneficio y métricas esenciales. |

El organizador ya tiene coordinados a los negocios. **DeTuristaAndo** no recluta participantes ni administra sus acuerdos comerciales.

## Experiencias que admite el modelo

| Experiencia | Participantes posibles | Ejemplo de meta |
|---|---|---|
| Feria del Libro | Librerías, editoriales y puestos. | Visitar 5 de 20 participantes. |
| Burger Week | Restaurantes con una propuesta especial. | Visitar 4 de 10 restaurantes. |
| Festival cervecero | Cervecerías, productores y puestos gastronómicos. | Visitar 5 participantes. |
| Ruta del Vino | Bodegas, viñedos, restaurantes y comercios relacionados. | Visitar 3 de 8 lugares, sin secuencia obligatoria. |
| Feria del Café de Especialidad | Cafeterías, tostadores y productores. | Visitar 4 participantes. |
| Noche cultural | Galerías, talleres, librerías y espacios culturales. | Conocer 4 espacios. |
| Experiencia para motociclistas | Talleres, miradores, cafés y alojamientos preparados. | Registrar 3 paradas elegidas libremente. |
| Turista en su propia ciudad | Negocios locales agrupados por temática. | Descubrir 4 lugares nuevos. |
| Trabajo remoto | Cafeterías y espacios aptos para trabajar. | Visitar 3 espacios durante la vigencia. |

El nombre comercial puede contener palabras como ruta, feria, festival o semana. En el dominio todas representan una **experiencia** y ninguna impone un itinerario.

## Actores

### Visitante

Descubre y participa en una experiencia. Puede ser turista o residente. No crea una cuenta en **DeTuristaAndo** ni proporciona datos personales para obtener su tarjeta.

### Organizador

Crea una cuenta convencional, configura la experiencia e invita a los negocios que ya coordinó. También define el beneficio, controla los accesos y consulta resultados agregados.

### Negocio participante

Activa un acceso limitado mediante un enlace de un solo uso, registra su dispositivo y define un PIN. Puede descargar material público, validar visitas, realizar canjes autorizados y consultar solamente su propia actividad.

### Operación de plataforma

Mantiene el servicio y puede actuar ante fraude, abuso o contenido indebido. No organiza las experiencias ni reemplaza al organizador.

## Cómo funciona

### 1. Crear y publicar

1. El organizador se registra con Google o correo y contraseña.
2. Configura identidad, fechas, ubicación, participantes, meta `K/N`, beneficio y canje.
3. Revisa la landing y la tarjeta mediante una vista previa.
4. Publica cuando la configuración obligatoria está completa.
5. Cada negocio recibe una invitación privada.

### 2. Activar un negocio

1. El negocio abre la invitación y confirma que reconoce al organizador y la experiencia.
2. Registra el dispositivo y define un PIN.
3. Descarga el QR público o copia el enlace para compartir la experiencia.
4. Accede al validador desde el mismo dispositivo.

El negocio no administra una cuenta completa ni puede consultar la actividad de otros participantes.

### 3. Descubrir y guardar la tarjeta

1. El visitante encuentra una experiencia desde la web, un enlace o un QR público.
2. Consulta participantes, ubicaciones, fechas, meta y beneficio.
3. Activa una participación anónima.
4. Guarda la tarjeta en Google Wallet.
5. Conserva una vista web privada con el detalle de su progreso.

### 4. Validar una visita

1. El visitante presenta el QR privado de su tarjeta o vista web.
2. El negocio lo escanea o introduce el código manual.
3. La plataforma muestra el contexto y el efecto antes de confirmar.
4. El negocio confirma la visita.
5. El progreso y la tarjeta se actualizan.

Una visita repetida se registra, pero solo la primera visita a cada negocio incrementa el progreso de lugares distintos.

### 5. Habilitar y canjear el beneficio

Al alcanzar `K` negocios distintos, la participación recibe un único beneficio. Un punto autorizado revisa sus condiciones y confirma el canje. La operación no puede repetirse ni reinicia el historial de visitas.

## La tarjeta de experiencia

Google Wallet es parte de MVP01. La tarjeta ofrece acceso rápido a:

- Identidad y vigencia de la experiencia.
- Progreso `K/N`.
- Estado del beneficio.
- QR privado de validación.
- Enlace a la vista web privada.

La landing pública contiene la información completa de la experiencia. La vista privada contiene el detalle de la participación. La base de datos de **DeTuristaAndo** conserva el estado oficial; Google Wallet lo representa y recibe actualizaciones.

Si Wallet no está disponible en un dispositivo, la vista web privada permite participar con el mismo flujo. Apple Wallet queda fuera de MVP01.

## Reglas esenciales

- La experiencia tiene vigencia y al menos dos negocios participantes.
- La meta cumple `2 ≤ K ≤ N`.
- El visitante elige el orden y los negocios que desea conocer.
- El QR público descubre la experiencia y nunca registra visitas.
- Solo un negocio autorizado confirma visitas y canjes.
- La credencial privada no contiene información personal legible.
- Las visitas repetidas no aumentan el progreso de negocios distintos.
- MVP01 crea como máximo un beneficio por participación.
- El canje es único e irreversible.
- El organizador es el único actor con una cuenta convencional.

El [diseño conceptual](docs/conceptual-design.md) define el vocabulario, los estados y las reglas completas.

## MVP01

MVP01 será gratuito y podrá desplegarse en producción. Su alcance cubre un flujo completo:

- Descubrimiento público con filtros simples y mapa.
- Registro del organizador con Google o credenciales.
- Creación, vista previa y publicación de experiencias.
- Invitación y acceso limitado para negocios.
- QR público y material imprimible.
- Participación anónima y vista web privada.
- Emisión y actualización de Google Wallet.
- Validación por cámara y código manual.
- Progreso por negocios distintos.
- Beneficio único y canje autorizado.
- Métricas operativas y auditoría esencial.

### Fuera de alcance

- Suscripciones, pagos y facturación.
- Marketplace, reservas, pedidos o logística.
- Postulación pública o negociación entre negocios.
- Aplicaciones móviles nativas y Apple Wallet.
- Puntos por compra, múltiples premios o campañas promocionales.
- Integraciones con cajas, ventas o inventarios.
- Inteligencia artificial durante la operación del producto.

### Indicadores iniciales

| Resultado | Indicador |
|---|---|
| La propuesta despierta interés. | Conversión de landing a tarjeta activada. |
| El negocio puede operar solo. | Activación y primera validación sin asistencia. |
| Existe tráfico cruzado. | Participaciones con una segunda visita en un negocio distinto. |
| La meta es alcanzable. | Tasa de finalización `K/N`. |
| El beneficio es útil. | Tasa de canje entre beneficios habilitados. |

La métrica principal es la proporción de participaciones que registran una segunda visita en un negocio diferente.

## Identidad visual

**DeTuristaAndo** tendrá una identidad oscura, enérgica y reconocible. Las landing pages usarán fondos casi negros, tarjetas en verde neón e ilustraciones de alto contraste con verdes, púrpuras y acentos complementarios. El lenguaje gráfico combinará formas orgánicas, trazos marcados, color intenso y movimiento breve. El resultado debe sentirse divertido, cool y chill sin perder claridad operativa.

El neón identifica descubrimiento, progreso y acciones principales. Los estados de error, advertencia o canje no dependerán solo del color. El sistema mantendrá contraste, foco visible y reducción de movimiento para quien la solicite.

Las [guías UI/UX](docs/ui-ux-guidelines.md) definen la dirección visual sin convertirla en una especificación rígida de píxeles.

## Tecnología y arquitectura

| Área | Decisión |
|---|---|
| Backend | PHP 8.4 y Laravel 13. |
| Base de inicio | Starter kit oficial de Laravel, variante Livewire. |
| Autenticación | Laravel Fortify y Laravel Socialite con Google. |
| Interfaz | Blade, Livewire 4, Alpine.js, Tailwind CSS 4 y Flux UI Free. |
| Datos | PostgreSQL 16. |
| Mapas | Leaflet y datos de OpenStreetMap. |
| Tarjeta | Google Wallet mediante un adaptador propio. |
| Pruebas | Pest o PHPUnit y Playwright. |
| Desarrollo | Laravel Sail con la aplicación, PostgreSQL, correo y dependencias locales en contenedores. |
| Producción | Dockerfile propio en la raíz y entrega mediante GitHub Actions. |
| Gestión | Lean, rolling wave y Kanban. |

El starter kit aporta autenticación, recuperación de acceso, verificación de correo, layouts, dashboard, componentes Flux y workflows iniciales. Socialite incorpora el acceso con Google sin delegar la autorización del producto.

Docker es la única dependencia obligatoria del host para desarrollar. Los comandos de PHP, Composer, Node y pruebas se ejecutan mediante Sail. Producción no reutiliza Sail: se construye desde el Dockerfile de la raíz y el item dedicado de CI deberá verificar esa imagen antes de promoverla.

La aplicación será un **monolito modular con arquitectura hexagonal**. Cada módulo separará dominio, casos de uso y adaptadores. DRY, KISS, YAGNI y SOLID se aplicarán donde reduzcan repetición, complejidad o acoplamiento; no como una obligación de crear capas o interfaces. Los patrones Service y Repository se usarán cuando exista una operación de negocio o una frontera de persistencia que los justifique.

Laravel cubrirá los controles web habituales. La aplicación añadirá autorización por alcance, separación de credenciales, idempotencia, auditoría y límites de intentos donde el dominio lo requiera. La documentación de [arquitectura](docs/architecture/overview.md) y [seguridad](docs/architecture/security.md) explica estas decisiones.

## Documentación

La documentación avanza desde el producto hacia la implementación. Cada archivo mantiene una responsabilidad y cambia junto con el código.

1. [Análisis de mercado](docs/market-analysis.md).
2. [Diseño conceptual](docs/conceptual-design.md).
3. [Guías UI/UX](docs/ui-ux-guidelines.md).
4. [Requisitos](docs/requirements.md).
5. [Arquitectura](docs/architecture/overview.md), [seguridad](docs/architecture/security.md) y ADR relacionados.
6. [Estrategia de calidad](docs/quality-strategy.md).
7. [Flujo de desarrollo](docs/development/workflow.md).

El [estándar de documentación](docs/documentation-standard.md) define autoridad, estilo y mantenimiento.

## Licencia

Este proyecto es software propietario con código fuente disponible para transparencia, revisión técnica y evaluación profesional. No es software de código abierto y su disponibilidad pública no concede permisos de uso, modificación, distribución ni explotación. Consulte los términos completos en [LICENSE](LICENSE).
