# Document AI Pipeline - README

## Architecture

```
DOCUMENT_AI/
├── src/
│   ├── api/
│   │   └── server.js          # Express API server
│   ├── processors/
│   │   ├── ocr.js             # Tesseract OCR processor
│   │   ├── textProcessor.js   # GPT-3.5 text analysis
│   │   └── documentParser.js  # Document structure parser
│   ├── utils/
│   │   ├── fileHandler.js     # File upload/download
│   │   ├── faIntegration.js   # FrontAccounting API
│   │   └── csvGenerator.js    # CSV export
│   └── templates/
│       └── documentSchema.js   # Output data schemas
├── uploads/                    # Raw uploads
├── processed/                   # OCR results
└── exports/                    # CSV/FA-ready files
```

## Features

1. **Upload Interface** - Drag & drop document upload
2. **Tesseract OCR** - Extract text from images (FREE)
3. **GPT-3.5 Turbo** - Classify and structure text (cheap)
4. **CSV Export** - Download structured data
5. **FA Integration** - Push directly to FrontAccounting

## Installation

```bash
cd DOCUMENT_AI
npm init -y
npm install express multer tesseract.js axios csv-writer dotenv
```

## Environment Variables (.env)

```env
# API Keys
OPENAI_API_KEY=your_openai_key_here

# FrontAccounting
FA_API_URL=http://your-fa-server/api
FA_USERNAME=admin
FA_PASSWORD=your_password

# Server
PORT=3000
UPLOAD_DIR=uploads
```

## Usage

1. Start server: `npm run dev`
2. Open browser: `http://localhost:3000`
3. Upload documents
4. AI extracts and structures data
5. Export CSV or push to FA

## Cost Analysis

| Operation | Cost |
|-----------|------|
| OCR (Tesseract) | FREE |
| GPT-3.5 Processing | ~$0.001 per document |
| 100 docs/day | ~$3/month |
| 500 docs/day | ~$15/month |

## Next Steps

- [x] Create project structure
- [ ] Build upload interface (Next.js)
- [ ] Implement Tesseract OCR
- [ ] Integrate GPT-3.5 Turbo
- [ ] Add CSV export
- [ ] Connect FA API
- [ ] Deploy V1
