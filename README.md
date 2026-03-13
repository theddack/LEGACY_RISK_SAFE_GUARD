# Legacy Risk Safe Guard

## Conversation Summary & Design Philosophy

## 대화 요약 및 설계 철학

------------------------------------------------------------------------

# 1. Background

# 1. 배경

**EN** - Modifying legacy ERP systems always carries uncertainty. -
Allowing AI to modify code blindly is risky. - A pre‑modification risk
calculation system is needed.

**KR** - 레거시 ERP 수정 전 항상 불안함이 존재함 - AI가 무작정 코드를
수정하는 것은 위험함 - 사전 위험 계산 시스템이 필요함

------------------------------------------------------------------------

# 2. System Philosophy

# 2. 시스템 철학

**EN**

AI is the modifier\
Risk Engine is the controller\
Humans are the final decision makers

**KR**

AI는 수정자\
Risk Engine은 통제자\
사람은 최종 결정자

------------------------------------------------------------------------

# 3. Core Structure

# 3. 핵심 구조

**EN**

User\
↓\
AI\
↓\
Risk Engine\
↓\
JSON\
↓\
AI Interpretation\
↓\
Human Decision

**KR**

사용자\
↓\
AI\
↓\
Risk Engine\
↓\
JSON\
↓\
AI 해석\
↓\
사람의 최종 결정

------------------------------------------------------------------------

# 4. Phased Development

# 4. 단계적 발전

**EN**

Phase 1: 70% Static Analysis\
Phase 2: AI Checklist System\
Phase 3: Risk-Based Modification Mode\
Phase 4: Automatic `risk_check()` before modification

**KR**

Phase 1: 70% 정적 분석\
Phase 2: AI 체크리스트 시스템\
Phase 3: Risk 기반 수정 모드\
Phase 4: 수정 전 자동 `risk_check()`

------------------------------------------------------------------------

# 5. Long-Term Goals

# 5. 장기 목표

**EN**

-   Multi-language expansion
-   API server architecture
-   Team‑shareable structure
-   Possibility of productization

**KR**

-   다중 언어 확장
-   API 서버화
-   팀 공유 가능한 구조
-   제품화 가능성 확보

------------------------------------------------------------------------

END
