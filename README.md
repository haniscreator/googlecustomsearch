# Google Custom Search – Search & Analytics Service

This project is a Laravel-based Search & Analytics backend designed with a clean architecture and microservice-style separation:

- **Search Service**
  - Exposes `/api/search`
  - Calls an external search provider (Google Custom Search)
  - Stores search history
  - Emits analytics events via queue

- **Analytics Service** (logical module in same app)
  - Consumes `search_performed` events
  - Stores analytics events in DB
  - Exposes admin analytics API & UI (`/admin/analytics`)

The stack uses:

- **Laravel + Breeze (Inertia + Vue)** for auth and admin UI
- **Google Custom Search JSON API** as external search provider
- **Queue jobs** for async analytics
- **Structured logs** that can be shipped to ELK/Sentry in production

---

## System Design Overview

High-level architecture (matching the original system design):

- **Client Side**
  - Browser / Frontend UI
  - External API clients (other backends)

- **Edge / Gateway**
  - NGINX / Load Balancer / API Gateway
  - Routes:
    - `/api/search` → Search Service
    - `/admin/...` → Admin (Inertia)
    - `/admin/analytics/summary` → Analytics summary API

- **Search Service (SearchApp)**
  - `HTTP Controllers` → `Actions` → `Services`
  - `SearchService` calls `SearchClient` → Google Custom Search
  - Writes `SearchHistory` to `Search DB`
  - Dispatches `RecordSearchPerformedJob` to queue

- **Analytics Service (AnalyticsApp)**
  - `RecordSearchPerformedJob` creates `AnalyticsEvent` records
  - `AnalyticsService` reads `analytics_events` table
  - `AnalyticsController` exposes:
    - Inertia page: `/admin/analytics`
    - JSON summary: `/admin/analytics/summary`

- **External Services**
  - External Search Provider:
    - **Google Custom Search JSON API**
  - Logs / ELK / Sentry:
    - Laravel logs (`storage/logs/laravel.log`) with structured events
  - Metrics / APM:
    - Can be hooked via Laravel log forwarding & APM agent

---

## Main Features

### Public Search API

`GET /api/search?q=keyword`

- Validates query (`q` required)
- Calls external search provider (Google Custom Search)
- Stores search history in DB
- Emits `search_performed` analytics events via a queued job
- Returns JSON:

```json
{
  "query": "cat",
  "results": {
    "items": [
      { "title": "...", "link": "...", "snippet": "..." },
      ...
    ],
    "searchInformation": {
      "totalResults": "..."
    }
  }
}
