/**
 * Word to HTML Converter Utility
 * Converts .docx files to HTML for viewing in WebView
 */

import * as fs from 'fs';
import mammoth from 'mammoth';

/**
 * Convert Word document to HTML
 */
export async function convertWordToHtml(filePath: string): Promise<string> {
  try {
    // Check if file exists
    if (!fs.existsSync(filePath)) {
      throw new Error(`Word document not found: ${filePath}`);
    }

    // Read the Word document
    const result = await mammoth.convertToHtml({ path: filePath });
    
    // Get the HTML content
    let html = result.value;
    
    // Get any messages (warnings, etc.)
    if (result.messages.length > 0) {
      console.warn('Word to HTML conversion messages:', result.messages);
    }

    // Remove inline font-size styles from mammoth output and normalize
    // This ensures consistent font sizes matching the Demand Letter format
    html = html.replace(/style="[^"]*font-size[^"]*"/gi, ''); // Remove font-size from inline styles
    html = html.replace(/font-size:\s*\d+[^;"]*/gi, ''); // Remove font-size from any remaining styles
    
    // Wrap in a styled HTML document matching the Demand Letter format
    const styledHtml = `
      <!DOCTYPE html>
      <html>
        <head>
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <style>
            * {
              font-size: 12px !important; /* Force consistent font size for all elements */
            }
            body {
              font-family: 'Times New Roman', serif;
              padding: 20px;
              background-color: #1a1a2e;
              color: #ffffff;
              line-height: 1.8;
              max-width: 800px;
              margin: 0 auto;
              font-size: 12px !important;
            }
            h1, h2, h3 {
              color: #ffffff;
              margin-top: 20px;
              margin-bottom: 10px;
              font-size: 16px !important; /* Consistent heading size */
            }
            h1 {
              text-align: center;
              border-bottom: 2px solid #4EFA8A;
              padding-bottom: 10px;
              font-size: 18px !important;
            }
            h2 {
              color: #ffffff;
              border-bottom: 2px solid #4EFA8A;
              padding-bottom: 10px;
              text-align: center;
              font-size: 16px !important;
            }
            p, div, span, li, td, th {
              color: #e0e0e0;
              margin: 8px 0;
              line-height: 1.6;
              font-size: 12px !important; /* Force 12px for all text elements */
              text-align: justify;
            }
            strong, b {
              color: #ffffff;
              font-weight: bold;
              font-size: 12px !important;
            }
            @media print {
              body {
                background-color: white;
                color: black;
              }
              h1, h2, h3 {
                color: black;
                border-bottom: 2px solid black;
              }
              p {
                color: black;
              }
            }
          </style>
        </head>
        <body>
          ${html}
        </body>
      </html>
    `;

    return styledHtml;
  } catch (error: any) {
    console.error('Error converting Word to HTML:', error);
    throw new Error(`Failed to convert Word document to HTML: ${error.message}`);
  }
}

