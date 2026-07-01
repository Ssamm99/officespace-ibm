# Guía rápida de arranque y apagado (Docker)

Instrucciones prácticas para levantar OfficeSpace y apagarlo de forma segura cada vez que quieras usarlo. Todos los comandos se ejecutan **desde la carpeta del proyecto**.

```bash
cd ~/Proyectos/officespace-ibm
```

> Ajusta la ruta si moviste la carpeta a otro sitio.

---

## Requisito

Tener **Docker Desktop** instalado y abierto. Antes de cualquier comando, verifica que Docker esté corriendo:

```bash
docker info
```

Si devuelve información del sistema, Docker está listo. Si da error, abre Docker Desktop y espera a que el ícono indique "running".

---

## Primera vez (solo una vez)

Si es la primera vez que levantas el proyecto en este equipo, crea tu archivo de configuración a partir de la plantilla:

```bash
cp .env.example .env
```

Luego abre `.env` y define un secreto para los tokens (`JWT_SECRET`). Puedes generar uno con:

```bash
openssl rand -hex 32
```

Copia el resultado en la línea `JWT_SECRET=...` del archivo `.env` y guarda.

> El archivo `.env` no se sube a GitHub, así que este paso hay que hacerlo una vez por equipo.

---

## Encender el proyecto

```bash
docker-compose up -d --build
```

- `up` levanta los contenedores.
- `-d` los deja corriendo en segundo plano (puedes cerrar la terminal).
- `--build` reconstruye la imagen; útil siempre que hayas cambiado código.

Espera unos segundos y verifica que todo esté arriba:

```bash
docker ps
```

Debes ver cinco contenedores en estado `Up`: `officespace_db`, `officespace_auth`, `officespace_catalog`, `officespace_booking` y `officespace_frontend`.

### Acceder

- Aplicación: http://localhost:8080/login.php
- Documentación de API: http://localhost:8080/api-docs.php

Credenciales de prueba:

| Rol | Email | Contraseña |
|---|---|---|
| Administrador | admin@corporativoalpha.com | Admin123 |
| Colaborador | carlos.mendez@corporativoalpha.com | User123 |

---

## Apagar el proyecto de forma segura

La forma correcta y recomendada:

```bash
docker-compose down
```

Esto detiene y elimina los contenedores **conservando tu base de datos** (el volumen `mysql_data`). Tus reservas y espacios siguen ahí la próxima vez que enciendas. Después puedes cerrar Docker Desktop con tranquilidad.

### Alternativas

| Quiero... | Comando |
|---|---|
| Apagar conservando datos (lo normal) | `docker-compose down` |
| Solo pausar sin eliminar contenedores (reinicio más rápido) | `docker-compose stop` |
| Reanudar lo que pausé con `stop` | `docker-compose start` |
| Apagar y **borrar la base de datos** (empezar de cero) | `docker-compose down -v` |

> Usa `down -v` solo si de verdad quieres perder los datos y volver al estado inicial de `init-db.sql`.

**No apagues cerrando Docker Desktop a la fuerza** sin ejecutar antes `docker-compose down` o `stop`: podrías dejar contenedores colgados o la base de datos en un estado inconsistente.

---

## Uso diario (resumen)

Una vez hecha la configuración inicial, tu rutina de cada día es solo esto:

```bash
# Para empezar a trabajar
cd ~/Proyectos/officespace-ibm
docker-compose up -d --build

# Para terminar
docker-compose down
```

---

## Solución de problemas

**"port is already allocated" / puerto ocupado.** Ya hay algo usando ese puerto (u otra instancia del proyecto encendida). Apaga con `docker-compose down` y vuelve a intentar. Si persiste, revisa qué usa el puerto con `lsof -i :8080`.

**La página carga pero no inicia sesión / no aparecen espacios.** Puede que la base de datos aún esté arrancando. Revisa los registros:

```bash
docker-compose logs -f
```

Cuando veas que MySQL está `ready for connections`, recarga la página con Ctrl+Shift+R.

**Cambié código y no se refleja.** Vuelve a levantar con `--build`:

```bash
docker-compose up -d --build
```

**Quiero verificar rápido que el backend responde.** Abre http://localhost:8001/get_spaces.php — debe devolver un JSON con los espacios.

**Empezar completamente limpio.** `docker-compose down -v` y luego `docker-compose up -d --build` recrea la base de datos desde cero con los datos de ejemplo.
