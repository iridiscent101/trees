---
paths:
  - 'app/{Actions,Models,Filament/Resources/TreeSurveyPoints,Support/Survey}/**'
---

# Survey

## Survey approval precedes tree conversion
Survey records use SurveyReviewStatus. Approval requires valid unique coordinates, an assigned campus area, and one resolved approved species name. Tree registration must only accept approved survey records and then mark them converted (stored as the legacy `verified` value).
