import fs from 'node:fs/promises';
import { FileBlob, SpreadsheetFile } from '@oai/artifact-tool';

const source = 'C:/xampp/htdocs/trees/.tmp-spreadsheet/trees-converted.xlsx';
const outputDir = 'C:/tmp/trees-invalid-audit';
await fs.mkdir(outputDir, { recursive: true });

const input = await FileBlob.load(source);
const workbook = await SpreadsheetFile.importXlsx(input);
const overview = await workbook.inspect({ kind: 'workbook,sheet,table', maxChars: 12000, tableMaxRows: 12, tableMaxCols: 30, tableMaxCellChars: 100 });
const reportParts = ['OVERVIEW', overview.ndjson];
const targetRanges = ['A388:F404', 'A558:F570', 'A628:F640'];

const sheets = [];
let dataQuality = null;
for (let index = 0; ; index += 1) {
  try {
    const sheet = workbook.worksheets.getItemAt(index);
    const used = sheet.getUsedRange(true);
    sheets.push({ index, name: sheet.name, address: used?.address ?? null });
    if (used) {
      if (index === 0) {
        const formulas = await workbook.inspect({ kind: 'formula', sheetId: sheet.name, range: used.address, maxChars: 12000, options: { maxResults: 50 } });
        reportParts.push('FORMULAS', formulas.ndjson);
      }
      if (index === 0) {
        const values = used.values;
        const headers = values[0].map(value => String(value ?? '').trim());
        const records = values.slice(1).map((row, rowIndex) => ({ row: rowIndex + 2, values: Object.fromEntries(headers.map((header, columnIndex) => [header, row[columnIndex]])) }));
        const number = value => value === null || value === '' ? null : Number(value);
        const distanceKm = (lat, lon) => {
          const radians = degrees => degrees * Math.PI / 180;
          const dLat = radians(lat - 16.480696);
          const dLon = radians(lon - 121.140771);
          const a = Math.sin(dLat / 2) ** 2 + Math.cos(radians(16.480696)) * Math.cos(radians(lat)) * Math.sin(dLon / 2) ** 2;
          return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        };
        const speciesCounts = new Map();
        const coordinateRows = new Map();
        const projectedRows = new Map();
        const missingByColumn = Object.fromEntries(headers.map(header => [header, 0]));
        const invalidRows = [];
        const outlierRows = [];
        const heights = [];
        let withinOneKm = 0;
        for (const record of records) {
          for (const header of headers) if (record.values[header] === null || record.values[header] === '') missingByColumn[header] += 1;
          const species = String(record.values.Species ?? '').trim();
          speciesCounts.set(species, (speciesCounts.get(species) ?? 0) + 1);
          const lat = number(record.values.Latitude), lon = number(record.values.Longitude), height = number(record.values.Height);
          if (height !== null && Number.isFinite(height)) heights.push(height);
          if (!Number.isFinite(lat) || !Number.isFinite(lon) || lat < -90 || lat > 90 || lon < -180 || lon > 180) invalidRows.push({ row: record.row, species, latitude: record.values.Latitude, longitude: record.values.Longitude, easting: record.values.Easting, northing: record.values.Northing });
          if (Number.isFinite(lat) && Number.isFinite(lon)) {
            const key = `${lat.toFixed(8)},${lon.toFixed(8)}`;
            if (!coordinateRows.has(key)) coordinateRows.set(key, []);
            coordinateRows.get(key).push(record.row);
            const distance = distanceKm(lat, lon);
            if (distance <= 1) withinOneKm += 1;
            if (distance > 1) outlierRows.push({ row: record.row, species, latitude: lat, longitude: lon, distanceKm: Number(distance.toFixed(2)), northing: record.values.Northing, easting: record.values.Easting });
          }
          const projectedKey = `${record.values.Easting},${record.values.Northing}`;
          if (!projectedRows.has(projectedKey)) projectedRows.set(projectedKey, []);
          projectedRows.get(projectedKey).push(record.row);
        }
        dataQuality = {
          headers,
          recordCount: records.length,
          uniqueSpecies: speciesCounts.size,
          missingByColumn,
          latitude: { min: Math.min(...records.map(record => number(record.values.Latitude)).filter(Number.isFinite)), max: Math.max(...records.map(record => number(record.values.Latitude)).filter(Number.isFinite)) },
          longitude: { min: Math.min(...records.map(record => number(record.values.Longitude)).filter(Number.isFinite)), max: Math.max(...records.map(record => number(record.values.Longitude)).filter(Number.isFinite)) },
          height: { min: Math.min(...heights), max: Math.max(...heights), over60Rows: records.filter(record => number(record.values.Height) > 60).map(record => ({ row: record.row, species: record.values.Species, height: record.values.Height })) },
          zeroHeightRows: records.filter(record => number(record.values.Height) === 0).map(record => record.row),
          withinOneKm,
          invalidCoordinateRows: invalidRows,
          coordinateDuplicateGroups: [...coordinateRows.entries()].filter(([, rows]) => rows.length > 1).map(([coordinate, rows]) => ({ coordinate, rows })),
          projectedCoordinateDuplicateGroups: [...projectedRows.entries()].filter(([, rows]) => rows.length > 1).map(([coordinate, rows]) => ({ coordinate, rows })),
          outlierRows,
          speciesWithNumericSuffix: records.filter(record => /\s*\d+$/.test(String(record.values.Species))).map(record => ({ row: record.row, species: record.values.Species })),
          normalizedUniqueSpecies: new Set(records.map(record => String(record.values.Species).trim().replace(/\s*\d+$/, '').trim().toLowerCase())).size,
          topSpecies: [...speciesCounts.entries()].sort((a, b) => b[1] - a[1]).slice(0, 25).map(([species, count]) => ({ species, count })),
        };
      }
      const detail = await workbook.inspect({ kind: 'region', sheetId: sheet.name, range: used.address, maxChars: 18000, tableMaxRows: 25, tableMaxCols: 35, tableMaxCellChars: 120 });
      reportParts.push(`SHEET_DETAIL ${index} ${sheet.name}`, detail.ndjson);
      if (index === 0) {
        for (const [targetIndex, targetRange] of targetRanges.entries()) {
          const targetDetail = await workbook.inspect({ kind: 'region', sheetId: sheet.name, range: targetRange, maxChars: 8000, tableMaxRows: 30, tableMaxCols: 10, tableMaxCellChars: 120 });
          reportParts.push(`TARGET_DETAIL ${targetRange}`, targetDetail.ndjson);
          const targetPreview = await workbook.render({ sheetName: sheet.name, range: targetRange, scale: 2, format: 'png' });
          await fs.writeFile(`${outputDir}/invalid-region-${targetIndex + 1}.png`, new Uint8Array(await targetPreview.arrayBuffer()));
        }
      }
      const preview = await workbook.render({ sheetName: sheet.name, autoCrop: 'all', scale: 1, format: 'png' });
      await fs.writeFile(`${outputDir}/sheet-${index + 1}.png`, new Uint8Array(await preview.arrayBuffer()));
    }
  } catch {
    break;
  }
}
console.log('SHEETS');
console.log(JSON.stringify(sheets));
await fs.writeFile(`${outputDir}/report.txt`, reportParts.join('\n'));
await fs.writeFile(`${outputDir}/data-quality.json`, JSON.stringify(dataQuality, null, 2));
