# Informe Técnico y de Seguridad — OfficeSpace

Este documento resume la arquitectura del sistema y el proceso de **revisión de seguridad** aplicado al proyecto. Cada hallazgo se presenta en formato **antes / después**, mostrando la vulnerabilidad detectada y la remediación implementada.

---

## 1. Arquitectura y Stack

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.3 (microservicios) |
| Base de datos | MySQL 8.0 (InnoDB, `utf8mb4`) |
| Frontend | HTML + Tailwind CSS, JavaScript vanilla, SweetAlert2, Chart.js |
| Infraestructura | Docker + Docker Compose (4 servicios + MySQL) |
| Autenticación | JWT (HS256) con verificación de firma |
| Documentación | OpenAPI 3 + Swagger UI |

**Patrón:** microservicios con base de datos compartida. Tres servicios independientes (`auth`, `catalog`, `booking`), cada uno con su propio contenedor y puerto, más un módulo `shared-infra` que centraliza configuración, conexión a BD y el middleware de autenticación/CORS.

---

## 2. Auditoría de Seguridad: Hallazgos y Remediación

El proyecto se sometió a una revisión de seguridad. Se identificaron ocho puntos de mejora, todos remediados. Los tres primeros eran críticos.

### 2.1 Verificación de firma del JWT (crítico)

**Riesgo:** sin verificar la firma, un atacante puede fabricar un token con rol `ADMINISTRADOR` y obtener acceso total.

| | |
|---|---|
| **Antes** | Los servicios decodificaban el *payload* del token con `base64_decode` y confiaban en él **sin recalcular ni comparar la firma HMAC**. |
| **Después** | `shared-infra/auth.php` recalcula la firma HMAC-SHA256 y la compara en **tiempo constante** (`hash_equals`), además de validar la expiración (`exp`). Un token manipulado se rechaza con 401. |

### 2.2 Inyección SQL (crítico)

**Riesgo:** manipulación de consultas para leer, modificar o borrar datos.

| | |
|---|---|
| **Antes** | El login concatenaba variables directamente en la consulta SQL (`... WHERE email = '$email'`). |
| **Después** | Todas las consultas que reciben datos del cliente usan **sentencias preparadas** con parámetros vinculados (`mysqli_prepare` / `bind_param`). |

### 2.3 Contraseñas en texto plano (crítico)

**Riesgo:** exposición directa de credenciales ante cualquier acceso a la base de datos.

| | |
|---|---|
| **Antes** | Las contraseñas se guardaban sin cifrar y se comparaban en texto plano. |
| **Después** | Se almacenan con `password_hash` (bcrypt) y se verifican con `password_verify`, con *re-hash* automático y migración de cuentas legadas. |

### 2.4 Secretos en el repositorio

| | |
|---|---|
| **Antes** | La contraseña de MySQL y el secreto JWT estaban escritos en `docker-compose.yml` y en el código. |
| **Después** | Se leen de variables de entorno (`.env`), excluido del control de versiones vía `.gitignore`. Se incluye `.env.example` como plantilla. |

### 2.5 CORS sin restricción

| | |
|---|---|
| **Antes** | `Access-Control-Allow-Origin: *` en todos los endpoints, incluidos los autenticados. |
| **Después** | Allowlist de orígenes configurable; solo se responde con la cabecera si el origen está autorizado. |

### 2.6 Datos sintéticos en la analítica

| | |
|---|---|
| **Antes** | El endpoint de métricas fabricaba datos con `rand()` y valores fijos cuando había pocas reservas. |
| **Después** | El dashboard refleja **exclusivamente datos reales** de la base de datos. |

### 2.7 Lógica de autenticación duplicada

| | |
|---|---|
| **Antes** | El bloque de validación del token estaba copiado en cada endpoint. |
| **Después** | Centralizado en un único middleware (`shared-infra/auth.php`), reutilizado por todos los servicios (principio DRY). |

### 2.8 Condición de carrera en reservas

**Riesgo:** dos usuarios reservando el mismo espacio y horario simultáneamente podían crear una doble reserva.

| | |
|---|---|
| **Antes** | La comprobación de solapamiento y la inserción eran pasos separados, sin transacción. |
| **Después** | La operación se ejecuta en una **transacción** con bloqueo de fila (`SELECT ... FOR UPDATE`), serializando las reservas concurrentes sobre el mismo espacio. |

---

## 3. Buenas Prácticas Destacadas

- **Validación de negocio en capas:** la creación de reservas aplica seis validaciones secuenciales (consistencia temporal, fechas/horas pasadas, horario de oficina, capacidad y no-solapamiento) usando la regla clásica de intersección de intervalos.
- **Modelo de datos sólido:** claves foráneas con integridad referencial, `ENUM` para estados controlados, *soft-delete* vía columna `activo` y *timestamps* automáticos.
- **Autorización a nivel de recurso:** la cancelación de una reserva verifica que el solicitante sea el dueño o un administrador.
- **Contrato de API documentado:** especificación OpenAPI 3 servida con Swagger UI.
- **Experiencia de usuario:** modo claro/oscuro persistente y sincronizado entre pestañas, y cierre de sesión automático ante un token expirado.

---

## 4. Decisiones Técnicas

**Microservicios con base de datos compartida.** Separa responsabilidades por dominio manteniendo la simplicidad transaccional de una única base, adecuado para el alcance del proyecto sin la complejidad de la coordinación distribuida.

**JWT sin librerías externas.** La emisión y verificación se implementan manualmente con HMAC-SHA256, centralizadas en `shared-infra/auth.php`, demostrando comprensión del mecanismo de firma en lugar de depender de una caja negra.

**Contenerización completa.** Toda la arquitectura se levanta con un solo comando (`docker-compose up`), garantizando un entorno reproducible e idéntico en cualquier máquina.

---

*Este informe documenta el estado del proyecto tras la revisión de seguridad. Todas las vulnerabilidades listadas en la sección 2 se encuentran corregidas en el código actual.*
