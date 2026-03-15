# PHP Analyzer 개선 내역 (변수 기반 테이블명 추적)

## 수정 배경
- SQL 문자열에 테이블명이 직접 적히지 않고, PHP 변수/프로퍼티를 통해 조립되는 경우가 있습니다.
- 기존 로직은 `FROM table_name` 같은 직접 표기 위주라서, 변수 기반 테이블 참조를 일부 놓칠 수 있었습니다.

## 이번에 수정한 내용
1. **변수/프로퍼티에 할당된 테이블명 해석 추가**
   - 예시 지원 형태
     - `$table = 'orders';`
     - `$this->table_name = 'staff_table';`
     - `private $tableName = 'erp.users';`
   - `DB.table` 형식은 `table`만 추출해 정규화하도록 처리했습니다.

2. **SQL 구문 내 변수 참조 패턴 추적 추가**
   - 직접 삽입 패턴: `FROM {$this->table_name}`
   - 문자열 결합 패턴: `FROM ' . $this->table_name`
   - 위 패턴에서 변수명이 매칭되면, 해당 변수에 할당된 테이블명을 최종 테이블 목록에 반영합니다.

3. **코드 주석 보강**
   - `extractTables()` 주석에 변수 기반 추적 동작과 예시를 추가했습니다.
   - 관련 private 메서드(`resolveTableVariables`, `extractTablesFromVariableReferences`, `normalizeTableName`)에 목적/동작 주석을 추가했습니다.

## 해결된 문제
- 테이블명이 문자열 리터럴로 직접 등장하지 않는 SQL에서도 테이블 후보를 추출할 수 있게 되어, 누락 가능성을 줄였습니다.
- `ERP_MAIN.staff_table` 같은 표기에서 스키마/DB명을 제거하고 실제 테이블명만 추출해 분석 일관성을 높였습니다.

## 유의사항
- 동적 조합이 매우 복잡한 경우(함수 반환값 다단계 결합, 런타임 외부입력 기반 생성)는 정적 분석 한계로 인해 완전 추적이 어려울 수 있습니다.
- 현재는 정확도/성능 균형을 위해 대표 패턴 중심으로 지원합니다.
