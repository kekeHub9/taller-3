<?php

namespace App\Services;

// Factory para generación de reportes

interface ReportExporter
{
    public function export($data);
    public function getFormat();
}

class PDFReport implements ReportExporter
{
    public function export($data)
    {
        // En producción usarías PDF 
        // Por ahora simulamos la funcionalidad, no funciona
        
        $html = $this->generateHTML($data);
        
        return response()->json([
            'success' => true,
            'format' => 'PDF',
            'filename' => 'reporte_asignaciones_' . date('Ymd_His') . '.pdf',
            'message' => 'Reporte PDF generado exitosamente (simulación)',
            'preview' => $html, // En realidad sería el PDF binario
            'data' => $data
        ]);
    }
    
    public function getFormat()
    {
        return 'pdf';
    }
    
    private function generateHTML($data)
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Reporte de Asignaciones - BioManage Sys</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #0d6efd; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f8f9fa; }
                .header { background: #f8f9fa; padding: 20px; border-radius: 5px; }
                .footer { margin-top: 30px; font-size: 12px; color: #6c757d; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>BioManage Sys - Reporte de Asignaciones</h1>
                <p>Generado: ' . $data['fecha_generacion'] . '</p>
                <p>Total de registros: ' . $data['total_registros'] . '</p>
            </div>';
        
        if (!empty($data['asignaciones'])) {
            $html .= '<table>
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th>Departamento</th>
                        <th>Responsable</th>
                        <th>Fecha Asignación</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($data['asignaciones'] as $asignacion) {
                $html .= '<tr>
                    <td>' . ($asignacion->equipo->nombre ?? 'N/A') . '</td>
                    <td>' . $asignacion->departamento . '</td>
                    <td>' . $asignacion->responsable . '</td>
                    <td>' . $asignacion->fecha_asignacion . '</td>
                    <td>' . $asignacion->estado . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
        }
        
        $html .= '<div class="footer">
                <p>Sistema BioManage Sys - Clínica NeuroVida</p>
                <p>© ' . date('Y') . ' - Todos los derechos reservados</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }
}

class ExcelReport implements ReportExporter
{
    public function export($data)
    {
        // Simulación de exportación Excel
        
        $filename = 'reporte_asignaciones_' . date('Ymd_His') . '.xlsx';
        
        return response()->json([
            'success' => true,
            'format' => 'Excel',
            'filename' => $filename,
            'message' => 'Reporte Excel generado exitosamente (simulación)',
            'download_url' => '#', // URL simulada, se buguea
            'data' => [
                'headers' => ['Equipo', 'Departamento', 'Responsable', 'Fecha', 'Estado'],
                'rows' => $this->prepareExcelData($data['asignaciones']),
                'statistics' => $data['estadisticas']
            ]
        ]);
    }
    
    public function getFormat()
    {
        return 'excel';
    }
    
    private function prepareExcelData($asignaciones)
    {
        return $asignaciones->map(function($asig) {
            return [
                $asig->equipo->nombre ?? 'N/A',
                $asig->departamento,
                $asig->responsable,
                $asig->fecha_asignacion,
                $asig->estado
            ];
        })->toArray();
    }
}

class CSVReport implements ReportExporter
{
    public function export($data)
    {
        $csvContent = "Equipo,Departamento,Responsable,Fecha Asignacion,Estado\n";
        
        foreach ($data['asignaciones'] as $asignacion) {
            $csvContent .= '"' . ($asignacion->equipo->nombre ?? 'N/A') . '",';
            $csvContent .= '"' . $asignacion->departamento . '",';
            $csvContent .= '"' . $asignacion->responsable . '",';
            $csvContent .= '"' . $asignacion->fecha_asignacion . '",';
            $csvContent .= '"' . $asignacion->estado . '"';
            $csvContent .= "\n";
        }
        
        $filename = 'reporte_asignaciones_' . date('Ymd_His') . '.csv';
        
        return response()->json([
            'success' => true,
            'format' => 'CSV',
            'filename' => $filename,
            'message' => 'Reporte CSV generado exitosamente',
            'content' => $csvContent, // dice pero no descarga
            'row_count' => count($data['asignaciones'])
        ]);
    }
    
    public function getFormat()
    {
        return 'csv';
    }
}

class ReportFactory
{
    public static function create($format)
    {
        return match(strtolower($format)) {
            'pdf' => new PDFReport(),
            'excel' => new ExcelReport(),
            'csv' => new CSVReport(),
            default => throw new \InvalidArgumentException("Formato no soportado: {$format}. Use: pdf, excel, csv")
        };
    }
    
    public static function getAvailableFormats()
    {
        return [
            'pdf' => 'Documento PDF',
            'excel' => 'Hoja de cálculo Excel',
            'csv' => 'Archivo CSV'
        ];
    }
}