# S4-01 — Migraciones red social (posts, reacciones, seguidores)

Tarea **bloqueante** del Sprint 4: base de datos para el feed tipo mini red de emprendedores.

## Tablas

### `emprendedor_posts`

Publicaciones del emprendedor (texto, imagen o video).

| Campo | Uso |
|-------|-----|
| `emprendedor_id` | Dueño del post |
| `tipo` | `texto`, `imagen`, `video` |
| `contenido` | Pie de foto / texto del post |
| `media_path` | Archivo en `storage/app/public` |
| `enlace_externo` | Video embebido (YouTube, TikTok) sin subir archivo |
| `estado` | `borrador`, `publicado`, `oculto` |
| `publicado_en` | Fecha visible en feed |
| `orden` | Orden manual en el perfil |

### `emprendedor_post_reacciones`

Una reacción por actor y post (`me_gusta`, `aplauso`, `apoyo`).

| Campo | Uso |
|-------|-----|
| `emprendedor_post_id` | Post |
| `actor_type` / `actor_id` | Morph: hoy `Visitante`; mañana también `User` (cuenta emprendedor E-03) |
| `tipo` | Tipo de reacción |

Índice único: no duplicar reacción del mismo actor en el mismo post.

### `emprendedor_seguidores`

Turista (`visitante_id`) sigue a un `emprendedor_id`.

Índice único: un visitante no puede seguir dos veces al mismo emprendedor.

## Modelos y enums

- `App\Models\EmprendedorPost`
- `App\Models\EmprendedorPostReaccion`
- `App\Models\EmprendedorSeguidor`
- `App\Enums\EmprendedorPostTipo`, `EmprendedorPostEstado`, `EmprendedorPostReaccionTipo`

Relaciones en `Emprendedor` (`posts`, `seguidoresVisitantes`) y `Visitante` (`emprendedoresSeguidos`, `reaccionesEnPosts`).

## Migrar en local

```bash
php artisan migrate
```

## Pruebas

```bash
php artisan test --filter=MigracionesRedSocial
```

## Siguientes tareas (no incluidas en S4-01)

- CRUD / API de posts para emprendedor o admin
- Botón seguir en perfil turista
- Feed que mezcle posts publicados con tarjetas de explorar
- Contadores de reacciones en UI
