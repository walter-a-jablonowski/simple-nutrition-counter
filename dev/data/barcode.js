// Cam access

navigator.mediaDevices.getUserMedia()

// Scan init

<script src="https://unpkg.com/@google/apis-client-library-for-web/client"></script>

gapi.load('https://www.googleapis.com/auth/barcode.scan', () => {
  // Barcode Scanning API is loaded and ready to use
});

// Scan init

const barcodeScanner = new gapi.barcodescanner.BarcodeScanner()
barcodeScanner.startScan((barcode) => {
  // Handle the scanned barcode data
  console.log(barcode)
})
