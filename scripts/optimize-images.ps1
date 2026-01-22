# Image Optimization Script for Windows
# Requires: Install sharp-cli globally with: npm install -g sharp-cli
# Or use online tools like TinyPNG, Squoosh.app, or ImageOptim

Write-Host "=== Image Optimization Guide ===" -ForegroundColor Cyan
Write-Host ""

# Current image sizes
Write-Host "Current Large Images (need optimization):" -ForegroundColor Yellow
Write-Host ""

$assets = Get-ChildItem -Path ".\assets" -Filter "*.png","*.jpeg","*.jpg" | 
    Sort-Object Length -Descending |
    Select-Object Name, @{Name="Size(MB)";Expression={[math]::Round($_.Length/1MB, 2)}}

$assets | Format-Table -AutoSize

Write-Host ""
Write-Host "=== Recommended Actions ===" -ForegroundColor Green
Write-Host ""
Write-Host "1. Use Squoosh.app (Free, Online):" -ForegroundColor White
Write-Host "   - Go to https://squoosh.app"
Write-Host "   - Upload each image"
Write-Host "   - Choose 'MozJPEG' for photos, 'OxiPNG' for graphics"
Write-Host "   - Set quality to 80-85%"
Write-Host "   - Target: Images under 200KB each"
Write-Host ""
Write-Host "2. Use TinyPNG (Free, Online):" -ForegroundColor White
Write-Host "   - Go to https://tinypng.com"
Write-Host "   - Drag and drop images"
Write-Host "   - Download compressed versions"
Write-Host ""
Write-Host "3. Resize large images:" -ForegroundColor White
Write-Host "   - Hero images: Max 1920px width"
Write-Host "   - Service icons: Max 400px width"
Write-Host "   - Process step icons: Max 300px width"
Write-Host ""
Write-Host "4. Convert to WebP format for even smaller sizes" -ForegroundColor White
Write-Host ""

# Calculate potential savings
$totalSize = ($assets | Measure-Object -Property "Size(MB)" -Sum).Sum
Write-Host "Current Total Size: $totalSize MB" -ForegroundColor Red
Write-Host "Target Total Size: ~2-3 MB (80-90% reduction)" -ForegroundColor Green
Write-Host ""
Write-Host "After optimization, your website will load 5-10x faster!" -ForegroundColor Cyan
