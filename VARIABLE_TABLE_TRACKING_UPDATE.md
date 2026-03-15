# PHP Analyzer Update Bundle / PHP Analyzer 개선 묶음

## 1) New additions in this round / 이번 라운드 추가 개선

### EN
Based on your latest feedback, the Risk Engine now provides three direct outputs:

1. **Query type analysis**
   - `db.query_locations[]` includes `type` (`SELECT`, `INSERT`, `UPDATE`, `DELETE`).
   - `db.query_type_counts` provides per-type counts and grouped counts (`read`, `write`).

2. **Table → query mapping**
   - `db.table_query_map` structure:
     - `{ "table_name": [{ line, type }, ...] }`

3. **Automatic related file inference**
   - `db.related_files` + `db.related_file_details` are generated from:
     - `dependency.inbound_paths`
     - file sets from `db.same_table_users`
   - `related_file_details` now includes `score`, `sources`, `confidence` for prioritization.

4. **Risk score model alignment**
   - Risk scoring now considers:
     - read queries (`query_read`)
     - write queries (`query_write`)
     - table count (`table_count`)
     - related file count (`related_file`)
   - Legacy `query_count` path is retained as backward-compatibility fallback.

### KR
최신 피드백 기준으로 Risk Engine이 아래를 직접 제공합니다.

1. **쿼리 타입 분석**
   - `db.query_locations[]`에 `type`(`SELECT`, `INSERT`, `UPDATE`, `DELETE`) 포함.
   - `db.query_type_counts`로 타입별/그룹별(`read`, `write`) 집계 제공.

2. **테이블 → 쿼리 매핑**
   - `db.table_query_map` 구조 제공:
     - `{ "table_name": [{ line, type }, ...] }`

3. **related files 자동 추론 + 신뢰도**
   - `db.related_files`, `db.related_file_details` 제공.
   - 생성 기준:
     - `dependency.inbound_paths`
     - `db.same_table_users` 파일 목록
   - `related_file_details`에 `score`, `sources`, `confidence`를 포함해 우선순위 탐색 가능.

4. **점수 모델 정렬 강화**
   - 리스크 점수에 아래를 반영:
     - 조회 쿼리(`query_read`)
     - 쓰기 쿼리(`query_write`)
     - 테이블 수(`table_count`)
     - 연관 파일 수(`related_file`)
   - 구버전 호환을 위해 `query_count` fallback 경로 유지.

---

## 2) Output shape summary / 출력 구조 요약

### EN
`metrics.db` now contains:
- `tables: string[]`
- `query_count: number`
- `query_type_counts: { SELECT, INSERT, UPDATE, DELETE, read, write }`
- `query_locations: [{ line, type, table, snippet }]`
- `table_query_map: { [tableName]: [{ line, type }] }`
- `same_table_users: { [tableName]: string[] }`
- `related_files: string[]`
- `related_file_details: [{ path, score, confidence, sources[] }]`

### KR
`metrics.db`는 현재 다음 항목을 제공합니다.
- `tables: string[]`
- `query_count: number`
- `query_type_counts: { SELECT, INSERT, UPDATE, DELETE, read, write }`
- `query_locations: [{ line, type, table, snippet }]`
- `table_query_map: { [tableName]: [{ line, type }] }`
- `same_table_users: { [tableName]: string[] }`
- `related_files: string[]`
- `related_file_details: [{ path, score, confidence, sources[] }]`
