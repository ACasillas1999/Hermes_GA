# Análisis Profesional: Hermes GA (Mensajería Masiva WABA)

## 🛠 Stack Tecnológico
*   **Backend:** PHP 8.2, Laravel 12.0
*   **Frontend:** HTML/Blade Templates, Vanilla JS/Axios, TailwindCSS v4, Vite
*   **Base de Datos y Entorno:** MySQL / SQLite, Redis
*   **Procesamiento Asíncrono:** Laravel Queues, Job Batching (`Bus::batch`)
*   **Integraciones API:** WhatsApp Business API (WABA) de Meta
*   **Herramientas:** PM2, Concurrently, Node.js

## 🚀 Funcionalidades Clave Desarrolladas
*   **Motor de Mensajería Masiva WABA:** Construí una plataforma centralizada para el envío de notificaciones y campañas a listas de empleados a través de WhatsApp de forma oficial.
*   **Sincronización Inteligente de Plantillas:** Módulo de conexión con Meta para descargar y mapear dinámicamente plantillas de WABA, detectando requerimientos de variables de texto y adjuntos multimedia en los encabezados.
*   **Programación y Calendario de Campañas:** Sistema de programación (Scheduling) que permite diferir el envío masivo de notificaciones, visualizado mediante un calendario interactivo desarrollado a medida.
*   **Monitoreo de Procesos en Lote:** Implementación de un dashboard en tiempo real para visualizar el progreso de miles de mensajes despachados, mostrando tasas de éxito y fallo por cada campaña (batch).
*   **Mi Rol Técnico:** Desarrollador Full-Stack principal. Fui responsable de la arquitectura de la base de datos, el diseño de la UI reactiva, la integración directa con la API de Meta y la implementación de toda la lógica de concurrencia y envío en segundo plano.

## 🏆 Logros Técnicos Destacables
*   **Procesamiento Escalable (Job Batching):** Resolví el desafío del envío masivo (miles de mensajes por campaña) delegando la carga a *background jobs* agrupados en lotes (`Bus::batch`). Esto evita timeouts de PHP, distribuye la carga de red y respeta el *rate limiting* de la API.
*   **Validación Estricta y Dinámica:** Creé algoritmos que validan pre-envío si el usuario proporcionó exactamente el número de parámetros (textos o URLs de archivos) requeridos por cada plantilla particular de Meta, evitando errores masivos de rechazo.
*   **Backfilling y Consistencia de Datos:** Implementé algoritmos de conciliación y *backfilling* en la base de datos para recuperar y enlazar registros de mensajes huérfanos con sus lotes programados, garantizando analíticas 100% exactas incluso en ventanas de alta latencia.
*   **UI/UX Premium:** Interfaz pulida y altamente responsiva utilizando TailwindCSS v4, garantizando una excelente experiencia de usuario para los administradores que gestionan las listas y envíos.

## 🧠 Soft Skills Implícitos
*   **Arquitectura Orientada a la Escalabilidad:** Pensamiento crítico al diseñar desde cero con colas de trabajo en lugar de bucles for síncronos, previendo el crecimiento transaccional.
*   **Resolución de Problemas Complejos:** Capacidad para entender y abstraer la rigurosa y a veces confusa documentación y estructura de datos de la API de Meta.
*   **Orientación a Resultados y Calidad:** Prevención proactiva de fallos (validación de variables de plantillas) y creación de herramientas robustas de monitoreo.

## 📄 Descripción para CV (2-3 líneas)
> Diseñé y desarrollé **Hermes GA**, una plataforma escalable de mensajería masiva integrada de forma nativa con la **API de WhatsApp Business (WABA)** utilizando **Laravel 12 y colas de trabajo asíncronas (Job Batching)**. Implementé la sincronización dinámica de plantillas multimedia y un motor de programación de campañas, logrando orquestar eficientemente el envío de miles de notificaciones simultáneas con monitoreo de progreso en tiempo real y sin bloqueos de servidor.
