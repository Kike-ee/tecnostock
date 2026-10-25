# TecnoStock · Registro de pruebas

**Entorno:** PHP 8.3, MariaDB 10.11 (equivalente a MySQL de XAMPP), servidor integrado de PHP. Fecha: 24-10-2026.
**Método:** script automatizado que ejecuta peticiones HTTP reales contra la aplicación y consulta la base de datos para verificar el resultado. Antes de cada corrida se reimporta `sql/tecnostock.sql`.
**Resultado global:** 69 de 69 pruebas automatizadas superadas, más la prueba de concurrencia.

**Datos de prueba usados** (del script SQL):

| Producto | Stock | Mínimo | Para qué sirve |
| --- | --- | --- | --- |
| ACC-001 Mouse inalámbrico | 25 | 5 | Salida exacta al stock y salida mayor al stock |
| ACC-002 Teclado mecánico | 3 | 5 | Producto en alerta; una entrada lo saca de la alerta |
| ACC-004 Audífonos Bluetooth | 8 | 8 | Límite: stock igual al mínimo no genera alerta |
| ACC-005 Cargador USB-C | 0 | 5 | Producto en alerta con stock cero |
| ACC-007 Webcam HD | 2 | 3 | Producto inactivo: no aparece ni admite movimientos |

## 1. Inicio de sesión

| ID | Caso | Resultado esperado | Resultado |
| --- | --- | --- | --- |
| L1 | Correo que no es un correo válido (`rike`) | Error "Ingresa un correo válido" | Pasó |
| L2 | Clave incorrecta | Error genérico "Correo o contraseña incorrectos" | Pasó |
| L3 | Token CSRF inválido | Error "Sesión inválida" | Pasó |
| L4 | Credenciales correctas | Redirige al inicio | Pasó |
| L5 | Acceder a una página protegida sin sesión | Redirige al login | Pasó |
| L6 | Cerrar sesión | Redirige al login y vuelve a exigir credenciales | Pasó |

## 2. Productos

| ID | Caso | Resultado esperado | Resultado |
| --- | --- | --- | --- |
| P1 | Listado por defecto | 6 productos activos; ACC-007 oculto | Pasó |
| P2 | Filtro de estado "todos" | 7 productos | Pasó |
| P3 | Búsqueda por nombre ("mouse") y por código ("ACC-003") | 1 resultado en cada caso | Pasó |
| P4 | Filtro por categoría | Solo productos de esa categoría | Pasó |
| P5 | Búsqueda con `%` | 0 resultados (no actúa como comodín) | Pasó |
| P6 | Búsqueda con `' OR 1=1 --` | 0 resultados (sin inyección SQL) | Pasó |
| P7 | Crear producto con stock inicial 7 | Producto creado, código en mayúsculas y movimiento ENTRADA de 7 | Pasó |
| P8 | Crear producto con código repetido | Error "Ya existe un producto con ese código"; sin duplicado | Pasó |
| P9 | Crear producto con precio negativo | Error de validación | Pasó |
| P10 | Nombre con etiquetas HTML | Se muestra como texto, no se interpreta | Pasó |
| P11 | Editar precio y categoría | Cambios guardados | Pasó |
| P12 | Intentar cambiar el stock desde la edición | El stock no cambia | Pasó |
| P13 | Desactivar producto con movimientos | Estado 0; el producto y sus movimientos se conservan | Pasó |
| P14 | Reactivar producto | Estado 1 | Pasó |
| P15 | Desactivar producto inexistente | Aviso "El producto no existe" | Pasó |
| P16 | Cambiar estado con token CSRF inválido o sin sesión | No se modifica nada | Pasó |

## 3. Movimientos de inventario

| ID | Caso | Resultado esperado | Resultado |
| --- | --- | --- | --- |
| M1 | Entrada de 10 unidades a ACC-002 (stock 3) | Stock pasa a 13; se registra el movimiento con tipo, cantidad, observación y usuario | Pasó |
| M2 | Tras M1, revisar la alerta del inicio | ACC-002 sale de la alerta | Pasó |
| M3 | Salida mayor al stock (26 sobre 25) | Error "Stock insuficiente"; stock y movimientos sin cambios | Pasó |
| M4 | Salida igual al stock (25 sobre 25) — caso límite | Se registra; stock queda en 0 | Pasó |
| M5 | Producto con stock 0 | Aparece en la alerta; otra salida es rechazada | Pasó |
| M6 | Cantidad 0, negativa, texto, decimal, vacía o mayor a 1.000.000 | Error "La cantidad debe ser un entero..." | Pasó |
| M7 | Tipo de movimiento inválido | Error de validación | Pasó |
| M8 | Producto vacío, inexistente o inactivo | Error de validación | Pasó |
| M9 | Observación de 256 caracteres | Error "hasta 255 caracteres" | Pasó |
| M10 | Token CSRF inválido | Error "Sesión inválida"; no se registra nada | Pasó |
| M11 | Observación con `<script>` | Se muestra escapada en el historial | Pasó |
| M12 | Mensaje de confirmación tras guardar | Se muestra una sola vez con el stock actual | Pasó |
| M13 | Filtros del historial por tipo y por producto | Solo muestra lo filtrado | Pasó |
| M14 | Integridad: stock de cada producto frente a la suma de entradas menos salidas | Coinciden en todos los productos | Pasó |
| M15 | Concurrencia: 8 salidas simultáneas de 2 unidades sobre un producto con stock 3 | 1 se registra, 7 son rechazadas, stock final 1 (nunca negativo) | Pasó |

## 4. Pruebas manuales en el navegador (por realizar antes de grabar el video)

Las validaciones en JavaScript no se pueden comprobar con el script automatizado. Verificar a mano:

- [ ] Login: enviar vacío y con un correo mal escrito muestra los mensajes en rojo sin recargar la página.
- [ ] Formulario de producto: escribir un código con símbolos o un precio negativo marca el campo al instante.
- [ ] Formulario de movimiento: elegir una salida mayor al stock del producto muestra "Stock insuficiente" antes de enviar.
- [ ] Todas las pantallas se ven bien en un ancho de pantalla de celular.
- [ ] El menú "Movimientos" queda resaltado en las pantallas del módulo.
