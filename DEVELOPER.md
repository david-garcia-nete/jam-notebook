# Jam Notebook — Developer Guide

This document contains commands and workflows for developing the Jam Notebook Laravel application using Docker (Laravel Sail).

---

## 🐳 Start Docker / Sail

```bash
./vendor/bin/sail up -d
```

---

## 🛑 Stop Docker / Sail

```bash
./vendor/bin/sail down
```

---

## 🗄️ Run Migrations

```bash
./vendor/bin/sail artisan migrate
```

---

## 🧹 Reset Database

```bash
./vendor/bin/sail artisan migrate:fresh
```

---

## 🧪 Run Tests

```bash
./vendor/bin/sail artisan test
```

---

## 📊 Run Tests with Coverage

```bash
./vendor/bin/sail artisan test --coverage-clover=coverage.xml
```

---

## 📦 Install PHP Dependencies

```bash
./vendor/bin/sail composer install
```

---

## 📦 Install Node Dependencies

```bash
./vendor/bin/sail npm install
```

---

## 🏗️ Build Frontend Assets

```bash
./vendor/bin/sail npm run build
```

---

## ⚡ Run Vite Dev Server

```bash
./vendor/bin/sail npm run dev
```

---

## 🖥️ Open Shell Inside Container

```bash
./vendor/bin/sail shell
```

---

## 🔍 Run Laravel Tinker

```bash
./vendor/bin/sail artisan tinker
```

---

## 📜 View Logs

```bash
./vendor/bin/sail logs
```

---

## 🌐 App URL

http://localhost

---

## 🔁 Typical Daily Workflow

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm run dev
```

In another terminal:

```bash
./vendor/bin/sail artisan test
```

---

## 🧠 Notes

* Always use `./vendor/bin/sail` instead of local PHP/Node
* Keep containers running during development
* Use `migrate:fresh` if database gets out of sync
* Run tests frequently to avoid regressions
