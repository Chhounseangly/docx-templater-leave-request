<?php

namespace App\Http\Controllers;

use App\Models\Template;
use DOMDocument;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use ZipArchive;

class LeaveTemplateController extends Controller
{
    public function index()
    {
        return Inertia::render('LeaveTemplates/Index', [
            'templates' => Template::get()
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'file' => 'required|file|mimes:docx|max:10240',
        ]);
        $path = $request->file('file')->store('templates', 'public');
        Template::create([
            'name' => $request->name,
            'file_path' => $path,
        ]);
        return redirect()->route('leave_template.index');
    }
    public function edit($id)
    {
        $template = Template::findOrFail($id);
        $fullPath = storage_path('app/public/' . $template->file_path);
        //extract only placeholders wrap by <>
        $place_holders = $this->extractPlaceholdersFromDocx($fullPath);
        return Inertia::render('LeaveTemplates/Edit', [
            'template' => $template,
            'db_mapping' => [
                'List Leave Type' => 'leave_types',
                'Employee Name'   => 'employee.name',
                'Employee ID'     => 'employee.code',
                'Department'      => 'employee.department',
                'Leave Type'      => 'leaves.type',
                'Reason'          => 'leaves.reason',
                'Start Date'      => 'leaves.start_date',
                'End Date'        => 'leaves.end_date',
                'Total Days'      => 'leaves.total_days',
                'Request Date'    => 'leaves.request_date',
                'Approver Name'   => 'approver.name',
                "Current Date"    => 'current_date',
            ],
            'place_holders' => $place_holders
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('LeaveTemplates/Create');
    }

    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);
        $template->update([
            'mapping' => $request->mapping,
        ]);
        return redirect()->route('leave_template.index');
    }
    function extractPlaceholdersFromDocx(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new Exception("Cannot open DOCX file");
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) {
            return [];
        }

        // Load XML
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml);

        $texts = [];

        // Collect all <w:t> nodes (DOCX text runs)
        foreach ($dom->getElementsByTagName('t') as $node) {
            $texts[] = $node->nodeValue;
        }

        // Join all text nodes (important because DOCX splits text)
        $fullText = implode('', $texts);

        // Decode entities just in case
        $fullText = html_entity_decode($fullText);

        preg_match_all('/\{([a-zA-Z0-9_.-]+)\}/', $fullText, $matches);
        // Return unique placeholder names (without {})
        return array_values(array_unique($matches[1]));
    }
    public function generatePdf($id)
    {
        //sample data of json 
        $sampleData = [
            'employee' => [
                'name'       => 'Sovannary',
                'code'       => 'EMP-1024',
                'department' => 'Human Resources',
            ],
            'leaves' => [
                [
                    'type'         => 'Annual Leave',
                    'reason'       => 'Personal Issue',
                    'start_date'   => '2026-02-03',
                    'end_date'     => '2026-02-07',
                    'total_days'   => 5,
                    'request_date' => '2026-01-28',
                ],
                [
                    'type'         => 'Sick Leave',
                    'reason'       => 'Personal Issue',
                    'start_date'   => '2026-01-03',
                    'end_date'     => '2026-01-07',
                    'total_days'   => 6,
                    'request_date' => '2026-01-29',
                ]
            ],
            'approver' => [
                'name' => 'Mr. Sokha Vuthy',
            ],
            'current_date' => '2026-01-29',
        ];

        //get from our system
        $leaveTypes = ['Sick Leave', 'Annual Leave', 'Maternity Leave'];

        $template = Template::findOrFail($id);
        $mapping = $template->mapping;
        $templatePath = storage_path('app/public/' . $template->file_path);
        $processor = new TemplateProcessor($templatePath);
        $processor->setMacroChars('{', '}');
        $phpWord = IOFactory::load($templatePath);
        $placeholders = $processor->getVariables();
        $placeholdersInTables = [];
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof Table) {
                    foreach ($element->getRows() as $row) {
                        foreach ($row->getCells() as $cell) {
                            $text = '';
                            foreach ($cell->getElements() as $cellElement) {
                                if (method_exists($cellElement, 'getText')) {
                                    $text .= $cellElement->getText();
                                }
                            }
                            foreach ($placeholders as $ph) {
                                if (str_contains($text, '{' . $ph . '}')) {
                                    $placeholdersInTables[] = $ph;
                                }
                            }
                        }
                    }
                    $key = array_search('leave_types', $template->mapping ?? []);
                    if (count($placeholdersInTables) > 0 && $key) {
                        $leavesByType = [];
                        foreach ($sampleData['leaves'] as $leave) {
                            $leavesByType[$leave['type']] = $leave;
                        }
                        $processor->cloneRow($key, count($leaveTypes));
                        foreach ($leaveTypes as $index => $typeName) {
                            $row = $index + 1;
                            foreach ($placeholdersInTables as $ph) {
                                if ($ph == $key) {
                                    $processor->setValue("{$ph}#{$row}", $typeName);
                                    continue;
                                }
                                $value = '';
                                if (isset($mapping[$ph])) {
                                    $valuePath = $mapping[$ph];
                                    if (strpos($valuePath, 'leaves.') === 0 && isset($leavesByType[$typeName])) {
                                        $value = $this->getNestedValue($leavesByType[$typeName], substr($valuePath, 7));
                                    } else {
                                        $value = $this->getNestedValue($sampleData, $valuePath);
                                    }
                                }
                                $processor->setValue("{$ph}#{$row}", $value);
                            }
                        }
                    } else {
                        foreach ($placeholdersInTables as $ph) {
                            $processor->setValue($ph, '');
                        }
                    }
                }
            }
        }

        // Handle normal placeholders (everything not in a table)
        $normalPlaceholders = array_diff($placeholders, $placeholdersInTables);

        foreach ($normalPlaceholders as $ph) {
            $value = '';
            if (isset($mapping[$ph])) {
                $keys = explode('.', $mapping[$ph]);
                $temp = $sampleData;
                foreach ($keys as $k) {
                    $temp = $temp[$k] ?? '';
                }
                $value = $temp;
            }
            $processor->setValue($ph, $value);
        }

        $outputPath = storage_path('app/public/generated/leave_request_' . time() . '.docx');
        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }
        $processor->saveAs($outputPath);
        // $pdfPath = $this->downloadDocxAsPdf();
        return response()->download($outputPath)->deleteFileAfterSend(true);;
    }
    function getNestedValue($data, $path)
    {
        $keys = explode('.', $path);
        $value = $data;
        foreach ($keys as $key) {
            if (is_array($value) && isset($value[$key])) {
                $value = $value[$key];
            } else {
                return ''; // fallback if missing
            }
        }
        return $value;
    }

    //function download file docx from s3 to pdf
    public function downloadDocxAsPdf($urlOfDocxFile = null)
    {
        $url = $urlOfDocxFile ?? 'https://res.cloudinary.com/drbdm4ucw/raw/upload/v1769744154/test-table-11_nlfuta.docx';
        $dir = storage_path('app/public/download_from_doc_to_pdf');

        // Create folder if not exists
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $tempDocxPath = $dir . '/temp_' . time() . '.docx';
        $content = Http::get($url)->body();
        file_put_contents($tempDocxPath, $content);

        $pdfPath = str_replace('.docx', '.pdf', $tempDocxPath);

        // Convert to PDF using LibreOffice
        $command = sprintf(
            'libreoffice --headless --convert-to pdf --outdir %s %s',
            escapeshellarg(dirname($tempDocxPath)),
            escapeshellarg($tempDocxPath)
        );
        exec($command);

        // Check PDF generation
        if (!file_exists($pdfPath)) {
            abort(500, 'PDF conversion failed');
        }

        // Delete temporary DOCX
        if (file_exists($tempDocxPath)) {
            unlink($tempDocxPath);
        }

        return $pdfPath;
    }
}
