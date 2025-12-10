# review.team.instruction.md

## Purpose

Provide a unified instruction set for the engineering team to ensure
consistent GitHub Copilot code reviews for Laravel + Livewire 3 projects
using SOLID principles and Vite-based asset workflows.

## Language Requirement

-   **Copilot must produce review output in Indonesian.**
-   Instructions themselves are in English for better technical
    comprehension.
-   Review output format is standardized (see below).

## Scope of Review

Copilot must evaluate and comment on the following areas:

### 1. Code Quality

-   Proper structure of controllers, Livewire components, jobs, and
    services.
-   Clean and readable code.
-   Consistent naming conventions.
-   No unused imports, variables, or dead code.
-   Proper separation of concerns using basic SOLID concepts.

### 2. Security

-   Validation using Laravel FormRequest or Livewire rules.
-   Protection against SQL Injection, XSS, CSRF.
-   No sensitive data exposure (password, token).
-   No hardcoded credentials.
-   Avoid risky string concatenation in queries.

### 3. Performance

-   Avoid N+1 queries.
-   Use caching appropriately.
-   Use pagination for large datasets.
-   Efficient Livewire component lifecycle usage.

### 4. Best Practice (Laravel + Livewire + SOLID)

-   Controllers thin, logic moved to Services or Actions.
-   Livewire: `mount`, `render`, and state management kept clean.
-   Single Responsibility Principle for services.
-   Avoid large God classes.
-   Use dependency injection.
-   Use Vite for asset building (`npm run build`, `npm run dev`).

------------------------------------------------------------------------

## Migration Rules (Strict)

-   **All migrations MUST use the `DB` class.**
-   Models cannot be used inside migrations.
-   Example allowed:

``` php
public function up(): void
{
    DB::statement("ALTER TABLE users ADD COLUMN uuid CHAR(36) NULL");
}
```

------------------------------------------------------------------------

## Output Format (Indonesian Only)

Copilot must generate review output in the following structure:

### 🧩 Ringkasan

Penjelasan singkat mengenai kondisi kode secara umum.

### 📝 Temuan

**Kualitas Kode:**\
- ...

**Keamanan:**\
- ...

**Performa:**\
- ...

**Best Practice & SOLID:**\
- ...

### 🔧 Saran Perbaikan

Berikan snippet kode yang telah diperbaiki.

### ⭐ Rekomendasi Akhir

-   **Disetujui**, atau\
-   **Perlu Perbaikan**, atau\
-   **Refactor Besar Dibutuhkan**

------------------------------------------------------------------------

## Team Collaboration Rules

-   All developers follow this same review format.
-   Copilot suggestions must be verified by a human reviewer.
-   Every new project feature should avoid introducing technical debt.
-   Code review must not alter business logic unless explicitly
    requested.
-   Copilot should ask clarification questions if context is incomplete.

------------------------------------------------------------------------

## Tone of Review

-   Professional
-   Clear and concise
-   Friendly but firm
-   Solution-oriented

------------------------------------------------------------------------

## Example Expected Output (in Indonesian)

### 🧩 Ringkasan

Komponen Livewire berfungsi tetapi belum mematuhi prinsip SOLID dan
terdapat potensi query berlebih.

### 📝 Temuan

**Kualitas Kode:**\
- Fungsi terlalu panjang.\
- Properti tidak dikelompokkan.

**Keamanan:**\
- Belum ada validasi input.

**Performa:**\
- Terjadi potensi N+1 query karena pemanggilan relasi di dalam loop.

**Best Practice & SOLID:**\
- Logika bisnis seharusnya dipindah ke Service.\
- Livewire menangani terlalu banyak proses sekaligus.

### 🔧 Saran Perbaikan

``` php
public function save()
{
    $this->validate();

    $this->service->storeData($this->form);
}
```

### ⭐ Rekomendasi Akhir

Perlu Perbaikan.

------------------------------------------------------------------------

## End of File
