# PHP Analyzer Update Bundle / PHP Analyzer 개선 묶음

## 1) What changed / 무엇을 수정했나

### EN
This bundle includes the previously requested improvements together:

1. **CLI usability improvements** (`risk.php`)
   - Added `--help` and explicit usage output.
   - Added validation messages for unsupported `--lang` and `--format`.
   - Shows usage on missing required options.

2. **Path safety validation** (`PhpAnalyzer::analyze`)
   - Added `realpath` failure handling for both root and target.
   - Added guard to ensure target file is located under root path.

3. **Comment-aware inbound detection consistency** (`scanAndAnalyze`)
   - Inbound include/require parsing now runs on comment-stripped content, matching the same-table detection approach.

4. **Variable-based table tracking (previous request) kept**
   - Variable/property table assignments are resolved and matched in SQL contexts.

5. **Searchable query locations added**
   - Added `db.query_locations` with line number and snippet for SQL keyword lines in the target file.
   - Purpose: make it easier to search by file/folder and jump to likely query locations.

### KR
요청하신 개선 항목들을 이번에 묶어서 반영했습니다.

1. **CLI 사용성 개선** (`risk.php`)
   - `--help` 및 사용법(usage) 출력 추가.
   - 지원하지 않는 `--lang`, `--format`에 대해 명확한 오류 메시지 추가.
   - 필수 옵션 누락 시 usage 표시.

2. **경로 안전성 검증 강화** (`PhpAnalyzer::analyze`)
   - `root`, `target` 모두 `realpath` 실패 처리 추가.
   - `target file`이 `root path` 하위인지 검증 추가.

3. **inbound 탐지의 주석 제거 일관화** (`scanAndAnalyze`)
   - inbound include/require 분석도 주석 제거 후 수행하도록 변경.
   - same-table 탐지와 동일 기준으로 오탐 감소.

4. **변수 기반 테이블 추적 유지 (이전 요청 반영분)**
   - 변수/프로퍼티에 담긴 테이블명을 SQL 문맥에서 추적하도록 유지.

5. **쿼리 위치 검색 정보 추가**
   - 대상 파일 기준 SQL 키워드 라인 정보를 `db.query_locations`로 제공.
   - 라인 번호 + snippet을 제공해 파일/폴더 단위로 추적하기 쉽게 개선.

---

## 2) What this solves / 무엇이 해결됐나

### EN
- Operational errors are easier to diagnose from CLI output.
- Invalid path combinations fail fast with clear reasons.
- False positives from comment-only include strings are reduced.
- SQL-related lines can now be located quickly without manually scanning full files.

### KR
- CLI 오류 원인 파악이 쉬워졌습니다.
- 잘못된 경로 조합을 초기에 명확히 차단합니다.
- 주석 내부 include 문자열로 인한 오탐을 줄였습니다.
- SQL 관련 위치를 수동 전체 스캔 없이 빠르게 찾을 수 있습니다.

---

## 3) Can we search file/folder or impacted query location? / 파일·폴더·영향 쿼리 위치 검색 가능 여부

### EN
Yes. With this update:
- Existing fields already provide impacted file-level references:
  - `dependency.inbound_paths`
  - `db.same_table_users`
- Newly added field provides line-level query hints in the target file:
  - `db.query_locations[] = { line, snippet }`

### KR
가능합니다. 이번 업데이트 기준으로:
- 기존에도 파일 단위 영향 정보는 제공됩니다.
  - `dependency.inbound_paths`
  - `db.same_table_users`
- 이번에 라인 단위 쿼리 위치 힌트도 추가했습니다.
  - `db.query_locations[] = { line, snippet }`

---

## 4) Current limitation / 현재 한계

### EN
- `query_locations` currently targets the analyzed file only.
- Highly dynamic SQL generation can still be partially missed in static analysis.

### KR
- `query_locations`는 현재 분석 대상 파일 중심입니다.
- 동적 SQL 조합이 매우 복잡한 경우 정적 분석 한계가 남아 있습니다.
