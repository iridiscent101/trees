import { execFileSync } from "node:child_process";
import fs from "node:fs/promises";
import path from "node:path";
import { SpreadsheetFile, Workbook } from "@oai/artifact-tool";

const projectDir = "C:/xampp/htdocs/trees";
const workingDir = `${projectDir}/.tmp-species-review`;
const outputDir = `${projectDir}/outputs/019fd052-b69e-70f3-8f81-f3751749a2f5`;
const outputPath = `${outputDir}/species-normalization-review.xlsx`;

const reportJson = execFileSync(
  "php",
  ["artisan", "tree-survey:audit-species", "--format=json"],
  { cwd: projectDir, encoding: "utf8", maxBuffer: 10 * 1024 * 1024 },
);
const report = JSON.parse(reportJson);
const safeText = (value) => {
  const text = value == null ? "" : String(value);
  return /^[=+\-@\t\r]/.test(text) ? `'${text}` : text;
};

const workbook = Workbook.create();
const summarySheet = workbook.worksheets.add("Decision Summary");
const groupsSheet = workbook.worksheets.add("Normalization Groups");
const labelsSheet = workbook.worksheets.add("Label Inventory");
const guideSheet = workbook.worksheets.add("Review Guide");

const green = "#075D41";
const greenLight = "#E8F2E9";
const gold = "#E4BE2F";
const border = "#D9E3DC";
const muted = "#5F6E65";
const headingFormat = {
  fill: green,
  font: { bold: true, color: "#FFFFFF" },
  verticalAlignment: "center",
};
const tableHeaderFormat = {
  fill: green,
  font: { bold: true, color: "#FFFFFF" },
  verticalAlignment: "center",
  wrapText: true,
  borders: { preset: "outside", style: "thin", color: border },
};

for (const sheet of [summarySheet, groupsSheet, labelsSheet, guideSheet]) {
  sheet.showGridLines = false;
}

summarySheet.mergeCells("A1:F2");
summarySheet.getRange("A1").values = [["NVSU Tree Survey — Species Normalization Review"]];
summarySheet.getRange("A1:F2").format = {
  fill: green,
  font: { bold: true, color: "#FFFFFF", size: 18 },
  verticalAlignment: "center",
};
summarySheet.getRange("A3:F3").merge();
summarySheet.getRange("A3").values = [["Read-only review package. Original survey labels remain unchanged until approved by an authorized forestry reviewer."]];
summarySheet.getRange("A3:F3").format = { fill: greenLight, font: { color: green, italic: true }, wrapText: true };

const summaryRows = Object.entries(report.summary).map(([metric, value]) => [safeText(metric), value]);
summarySheet.getRange(`A5:B${4 + summaryRows.length}`).values = summaryRows;
summarySheet.getRange("B5").format.numberFormat = "yyyy-mm-dd hh:mm";
summarySheet.getRange("A5:A13").format = { font: { bold: true, color: green }, fill: "#F7FAF7" };
summarySheet.getRange("A5:B13").format.borders = { preset: "outside", style: "thin", color: border };
summarySheet.getRange("D5:E5").values = [["Reviewer progress", "Count"]];
summarySheet.getRange("D5:E5").format = tableHeaderFormat;

const groupStartRow = 4;
const groupLastRow = groupStartRow + report.groups.length - 1;
summarySheet.getRange("D6:D10").values = [["Pending"], ["Approved"], ["Merge"], ["Split"], ["Rejected"]];
summarySheet.getRange("E6:E10").formulas = [
  [`=COUNTIF('Normalization Groups'!$I$${groupStartRow}:$I$${groupLastRow},"Pending")`],
  [`=COUNTIF('Normalization Groups'!$I$${groupStartRow}:$I$${groupLastRow},"Approve")`],
  [`=COUNTIF('Normalization Groups'!$I$${groupStartRow}:$I$${groupLastRow},"Merge")`],
  [`=COUNTIF('Normalization Groups'!$I$${groupStartRow}:$I$${groupLastRow},"Split")`],
  [`=COUNTIF('Normalization Groups'!$I$${groupStartRow}:$I$${groupLastRow},"Reject")`],
];
summarySheet.getRange("D6:E10").format.borders = { preset: "outside", style: "thin", color: border };
summarySheet.getRange("D12:E12").values = [["Approved records", "Count"]];
summarySheet.getRange("D12:E12").format = tableHeaderFormat;
summarySheet.getRange("D13").values = [["Records covered by approved groups"]];
summarySheet.getRange("E13").formulas = [[`=SUMIF('Normalization Groups'!$I$${groupStartRow}:$I$${groupLastRow},"Approve",'Normalization Groups'!$C$${groupStartRow}:$C$${groupLastRow})`]];
summarySheet.getRange("D13:E13").format.borders = { preset: "outside", style: "thin", color: border };
summarySheet.getRange("A1:A13").format.columnWidth = 47;
summarySheet.getRange("B1:B13").format.columnWidth = 24;
summarySheet.getRange("C1:C13").format.columnWidth = 4;
summarySheet.getRange("D1:D13").format.columnWidth = 40;
summarySheet.getRange("E1:E13").format.columnWidth = 15;
summarySheet.getRange("F1:F13").format.columnWidth = 4;

groupsSheet.mergeCells("A1:K1");
groupsSheet.getRange("A1").values = [["Species normalization groups — reviewer decisions are entered in columns I–K"]];
groupsSheet.getRange("A1:K1").format = headingFormat;
groupsSheet.getRange("A2:K2").merge();
groupsSheet.getRange("A2").values = [["Approve only mechanically safe labels. Spelling suggestions, combined labels, synonyms, and taxonomy require forestry verification."]];
groupsSheet.getRange("A2:K2").format = { fill: greenLight, font: { color: green, italic: true }, wrapText: true };
const groupHeaders = [["Normalized Key", "Proposed Label", "Records", "Exact Labels", "Classification", "Possible Existing Match", "Similarity %", "Recorded Variants", "Reviewer Decision", "Approved Label", "Reviewer Notes"]];
groupsSheet.getRange("A3:K3").values = groupHeaders;
groupsSheet.getRange("A3:K3").format = tableHeaderFormat;
const groupRows = report.groups.map((group) => [
  safeText(group.normalized_key),
  safeText(group.proposed_label),
  group.records,
  group.exact_label_count,
  safeText(group.classification),
  safeText(group.suggested_existing_group),
  group.similarity_percent == null ? null : group.similarity_percent / 100,
  safeText(group.variants.join(" | ")),
  "Pending",
  "",
  "",
]);
groupsSheet.getRange(`A4:K${groupLastRow}`).values = groupRows;
groupsSheet.getRange(`G4:G${groupLastRow}`).format.numberFormat = "0.0%";
groupsSheet.getRange(`H4:H${groupLastRow}`).format.wrapText = true;
groupsSheet.getRange(`K4:K${groupLastRow}`).format.wrapText = true;
groupsSheet.getRange(`I4:I${groupLastRow}`).dataValidation = { rule: { type: "list", values: ["Pending", "Approve", "Merge", "Split", "Reject"] } };
groupsSheet.tables.add(`A3:K${groupLastRow}`, true, "SpeciesNormalizationGroups");
groupsSheet.freezePanes.freezeRows(3);
groupsSheet.getRange(`E4:E${groupLastRow}`).conditionalFormats.add("containsText", { text: "possible_spelling_variant", format: { fill: "#FFF3CD", font: { color: "#7A4B00" } } });
groupsSheet.getRange(`E4:E${groupLastRow}`).conditionalFormats.add("containsText", { text: "combined_species_label", format: { fill: "#FDE2E1", font: { color: "#8A2723" } } });
groupsSheet.getRange(`E4:E${groupLastRow}`).conditionalFormats.add("containsText", { text: "mechanical_cleanup", format: { fill: "#E4F4E8", font: { color: green } } });
groupsSheet.getRange(`I4:I${groupLastRow}`).conditionalFormats.add("containsText", { text: "Pending", format: { fill: "#FFF7D6", font: { color: "#765D00" } } });
const groupWidths = [24, 24, 11, 12, 28, 25, 13, 72, 20, 24, 42];
groupWidths.forEach((width, index) => groupsSheet.getRangeByIndexes(0, index, groupLastRow, 1).format.columnWidth = width);

labelsSheet.mergeCells("A1:I1");
labelsSheet.getRange("A1").values = [["Original label inventory — source values and their proposed review group"]];
labelsSheet.getRange("A1:I1").format = headingFormat;
const labelHeaders = [["Original Label", "Records", "Normalized Key", "Proposed Label", "Numeric Identifier", "Classification", "Possible Existing Match", "Similarity %", "Mechanically Safe"]];
labelsSheet.getRange("A3:I3").values = labelHeaders;
labelsSheet.getRange("A3:I3").format = tableHeaderFormat;
const labelRows = report.labels.map((label) => [
  safeText(label.original_label),
  label.records,
  safeText(label.normalized_key),
  safeText(label.proposed_label),
  label.has_numeric_identifier ? "Yes" : "No",
  safeText(label.classification),
  safeText(label.suggested_existing_group),
  label.similarity_percent == null ? null : label.similarity_percent / 100,
  label.safe_to_apply_automatically ? "Yes" : "No",
]);
const labelLastRow = 3 + labelRows.length;
labelsSheet.getRange(`A4:I${labelLastRow}`).values = labelRows;
labelsSheet.getRange(`H4:H${labelLastRow}`).format.numberFormat = "0.0%";
labelsSheet.tables.add(`A3:I${labelLastRow}`, true, "OriginalSpeciesLabels");
labelsSheet.freezePanes.freezeRows(3);
labelsSheet.getRange(`I4:I${labelLastRow}`).conditionalFormats.add("containsText", { text: "Yes", format: { fill: "#E4F4E8", font: { color: green, bold: true } } });
labelsSheet.getRange(`I4:I${labelLastRow}`).conditionalFormats.add("containsText", { text: "No", format: { fill: "#FDE2E1", font: { color: "#8A2723", bold: true } } });
const labelWidths = [29, 11, 25, 25, 19, 28, 26, 13, 19];
labelWidths.forEach((width, index) => labelsSheet.getRangeByIndexes(0, index, labelLastRow, 1).format.columnWidth = width);

guideSheet.mergeCells("A1:D1");
guideSheet.getRange("A1").values = [["Reviewer guide"]];
guideSheet.getRange("A1:D1").format = headingFormat;
guideSheet.getRange("A3:D3").values = [["Classification", "Meaning", "Recommended Decision", "Safety"]];
guideSheet.getRange("A3:D3").format = tableHeaderFormat;
guideSheet.getRange("A4:D7").values = [
  ["mechanical_cleanup", "Number, spacing, or casing differs while the normalized base label is the same.", "Approve after checking the proposed display label.", "Eligible for mechanical cleanup"],
  ["possible_spelling_variant", "A similar existing label was detected by edit distance.", "Merge only after forestry confirmation.", "Never automatic"],
  ["combined_species_label", "One source cell appears to contain two species labels.", "Inspect the individual survey record and split or correct it.", "Never automatic"],
  ["consistent_label", "Only one exact label exists in its normalized group.", "Verify taxonomy separately; no spelling cleanup is proposed.", "No normalization needed"],
];
guideSheet.getRange("A9:D9").values = [["Reviewer Decision", "Use When", "Approved Label", "Notes"]];
guideSheet.getRange("A9:D9").format = tableHeaderFormat;
guideSheet.getRange("A10:D14").values = [
  ["Approve", "The proposed label is correct.", "Enter the final display label.", "Optional"],
  ["Merge", "This group is the same species as another approved group.", "Enter the target approved label.", "Explain the evidence"],
  ["Split", "A combined or ambiguous group contains multiple species.", "Leave blank until individual rows are corrected.", "Required"],
  ["Reject", "The proposal is incorrect.", "Enter a replacement only if known.", "Required"],
  ["Pending", "Review is incomplete.", "Leave blank.", "Optional"],
];
guideSheet.getRange("A4:D14").format = { wrapText: true, verticalAlignment: "top", borders: { preset: "inside", style: "thin", color: border } };
[25, 58, 49, 32].forEach((width, index) => guideSheet.getRangeByIndexes(0, index, 14, 1).format.columnWidth = width);

await fs.mkdir(workingDir, { recursive: true });
await fs.mkdir(outputDir, { recursive: true });

const inspections = [];
inspections.push((await workbook.inspect({ kind: "table", range: "Decision Summary!A1:F13", include: "values,formulas", tableMaxRows: 20, tableMaxCols: 8 })).ndjson);
inspections.push((await workbook.inspect({ kind: "table", range: "Normalization Groups!A1:K14", include: "values,formulas", tableMaxRows: 15, tableMaxCols: 12 })).ndjson);
inspections.push((await workbook.inspect({ kind: "match", searchTerm: "#REF!|#DIV/0!|#VALUE!|#NAME\\?|#N/A", options: { useRegex: true, maxResults: 100 }, summary: "formula error scan" })).ndjson);
await fs.writeFile(`${workingDir}/inspection.ndjson`, inspections.join("\n"), "utf8");

const previews = [
  [summarySheet, "A1:F14", "summary.png"],
  [groupsSheet, "A1:K16", "groups.png"],
  [labelsSheet, "A1:I18", "labels.png"],
  [guideSheet, "A1:D14", "guide.png"],
];
for (const [sheet, range, filename] of previews) {
  const preview = await workbook.render({ sheetName: sheet.name, range, scale: 1.2, format: "png" });
  await fs.writeFile(path.join(workingDir, filename), new Uint8Array(await preview.arrayBuffer()));
}

const output = await SpreadsheetFile.exportXlsx(workbook);
await output.save(outputPath);
console.log(JSON.stringify({ outputPath, groups: report.groups.length, labels: report.labels.length, summary: report.summary }, null, 2));
