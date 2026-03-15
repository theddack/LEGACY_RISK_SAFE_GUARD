# Legacy Risk Safe Guard

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Language](https://img.shields.io/badge/language-PHP-blue.svg)]
[![Static Analysis](https://img.shields.io/badge/type-static--analysis-orange.svg)]

A static analysis tool that helps prevent risky AI-assisted modifications in legacy codebases.

레거시 코드베이스에서 AI 기반 코드 수정 시 발생할 수 있는 위험을 줄이기 위한 정적 분석 도구입니다.

---

# ⚠️ The Problem

AI coding tools can modify code extremely fast.

But legacy systems are fragile.

A small change in one file may affect:

- multiple dependent files
- shared database tables
- hidden runtime logic
- unexpected production issues

---

AI 코딩 도구는 코드를 매우 빠르게 수정할 수 있습니다.

하지만 레거시 시스템은 매우 취약한 구조를 가지고 있습니다.

한 파일의 작은 수정이 다음과 같은 문제를 일으킬 수 있습니다.

- 여러 파일 의존성 영향
- 공유된 데이터베이스 테이블 영향
- 숨겨진 런타임 로직
- 예상치 못한 운영 장애

---

# 🛡️ The Solution

Legacy Risk Safe Guard analyzes legacy code before modification.

It detects:

- file complexity
- dependency relationships
- database usage
- shared table impact
- SQL query locations

This helps developers **understand modification risk before changing code.**

---

Legacy Risk Safe Guard는 코드 수정 전에 레거시 코드를 분석합니다.

다음 항목들을 탐지합니다.

- 파일 복잡도
- 파일 의존성 관계
- 데이터베이스 사용
- 공유 테이블 영향
- SQL 쿼리 위치

이를 통해 **코드를 수정하기 전에 위험도를 파악할 수 있습니다.**

---

# 🔍 Example Analysis

# Legacy Risk Safe Guard

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Language](https://img.shields.io/badge/language-PHP-blue.svg)]
[![Static Analysis](https://img.shields.io/badge/type-static--analysis-orange.svg)]

A static analysis tool that helps prevent risky AI-assisted modifications in legacy codebases.

레거시 코드베이스에서 AI 기반 코드 수정 시 발생할 수 있는 위험을 줄이기 위한 정적 분석 도구입니다.

---

# ⚠️ The Problem

AI coding tools can modify code extremely fast.

But legacy systems are fragile.

A small change in one file may affect:

- multiple dependent files
- shared database tables
- hidden runtime logic
- unexpected production issues

---

AI 코딩 도구는 코드를 매우 빠르게 수정할 수 있습니다.

하지만 레거시 시스템은 매우 취약한 구조를 가지고 있습니다.

한 파일의 작은 수정이 다음과 같은 문제를 일으킬 수 있습니다.

- 여러 파일 의존성 영향
- 공유된 데이터베이스 테이블 영향
- 숨겨진 런타임 로직
- 예상치 못한 운영 장애

---

# 🛡️ The Solution

Legacy Risk Safe Guard analyzes legacy code before modification.

It detects:

- file complexity
- dependency relationships
- database usage
- shared table impact
- SQL query locations

This helps developers **understand modification risk before changing code.**

---

Legacy Risk Safe Guard는 코드 수정 전에 레거시 코드를 분석합니다.

다음 항목들을 탐지합니다.

- 파일 복잡도
- 파일 의존성 관계
- 데이터베이스 사용
- 공유 테이블 영향
- SQL 쿼리 위치

이를 통해 **코드를 수정하기 전에 위험도를 파악할 수 있습니다.**

---

# 🔍 Example Analysis
Legacy Risk Safe Guard Report

File: work_food/package_list.php

Risk Level: HIGH (7.1)

Complexity
LOC: 180
Outbound includes: 7

Database Impact
Tables: food_package, nutrition_unit

Query Locations
Line 120: SELECT ...
Line 250: UPDATE ..


This report helps developers quickly understand **where risks exist**.

---

이 분석 결과를 통해 개발자는 **어디에서 위험이 발생할 수 있는지 빠르게 파악할 수 있습니다.**

---

# ⚙️ Architecture
Developer / AI Tool
↓
Legacy Risk Safe Guard
↓
Risk Analysis
↓
Risk Report
↓
Safe Code Modification


---

개발자 또는 AI 도구가 코드를 수정하기 전에  
Risk Engine을 통해 위험 분석을 수행할 수 있습니다.

---

# 🚀 Usage

Analyze a file:
php risk.php
--lang=php
--root=/path/to/project
--target=/path/to/file.php

php risk.php --help

Example:

php risk.php --lang=php --root=/project --target=/project/test.php


---

# 📊 What it analyzes

- Lines of code (LOC)
- Inbound dependencies
- Outbound includes
- Database table usage
- Shared table impact across the project
- SQL query locations

---

# 🧩 Current Support

Supported language:

- PHP

Planned support:

- Python
- Node.js
- Java

---

# ⚠️ Limitations

- Dynamic SQL generation may not always be detected
- Static analysis cannot fully understand runtime behavior
- Query location detection focuses on the analyzed file

---

# 📄 License

MIT License

This project is licensed under the MIT License.