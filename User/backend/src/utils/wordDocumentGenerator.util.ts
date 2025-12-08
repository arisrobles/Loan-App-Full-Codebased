/**
 * Word Document Generator Utility
 * Generates .docx files from legal document text
 */

import { Document, Packer, Paragraph, HeadingLevel, AlignmentType, TextRun } from 'docx';
import * as fs from 'fs';
import * as path from 'path';

interface WordDocumentOptions {
  title: string;
  content: string;
  fileName: string;
  outputDir: string;
}

/**
 * Convert plain text to Word document paragraphs
 * Preserves template structure, formatting, and layout
 */
function textToParagraphs(text: string): Paragraph[] {
  const lines = text.split('\n');
  const paragraphs: Paragraph[] = [];

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    const trimmed = line.trim();
    
    // Preserve empty lines exactly as they are
    if (trimmed === '') {
      paragraphs.push(
        new Paragraph({
          spacing: {
            after: 120, // 6pt spacing for blank lines
          },
        })
      );
      continue;
    }

    // Check for "Republic of the Philippines }" header pattern
    if (trimmed.includes('Republic of the Philippines') && trimmed.includes('}')) {
      paragraphs.push(
        new Paragraph({
          text: trimmed,
          alignment: AlignmentType.CENTER,
          spacing: {
            before: 120,
            after: 60,
          },
        })
      );
      continue;
    }

    // Check for "City of ... } S.S." pattern
    if (trimmed.includes('City of') && trimmed.includes('} S.S.')) {
      paragraphs.push(
        new Paragraph({
          text: trimmed,
          alignment: AlignmentType.CENTER,
          spacing: {
            before: 60,
            after: 240, // Extra space after header
          },
        })
      );
      continue;
    }

    // Check if line is a document title (centered, bold)
    const isDocumentTitle = (
      trimmed === 'PERSONAL LOAN AGREEMENT' ||
      trimmed === 'GUARANTY AGREEMENT' ||
      trimmed === 'FINAL DEMAND FOR UNPAID PERSONAL LOAN' ||
      trimmed === 'FINAL DEMAND LETTER FOR PAYMENT'
    );

    if (isDocumentTitle) {
      paragraphs.push(
        new Paragraph({
          text: trimmed,
          heading: HeadingLevel.HEADING_1,
          alignment: AlignmentType.CENTER,
          spacing: {
            before: 240, // 12pt before
            after: 240, // 12pt after
          },
        })
      );
      continue;
    }

    // Check for "Subject:" line (centered, bold-like)
    if (trimmed.startsWith('Subject:')) {
      paragraphs.push(
        new Paragraph({
          text: trimmed,
          alignment: AlignmentType.LEFT,
          spacing: {
            before: 240,
            after: 120,
          },
        })
      );
      continue;
    }

    // Check for "KNOW ALL MEN BY THESE PRESENTS:" (centered, all caps)
    if (trimmed === 'KNOW ALL MEN BY THESE PRESENTS:' || trimmed === 'WITNESSETH:') {
      paragraphs.push(
        new Paragraph({
          text: trimmed,
          alignment: AlignmentType.CENTER,
          spacing: {
            before: 120,
            after: 120,
          },
        })
      );
      continue;
    }

    // Check for numbered sections (1., 2., 3., etc. or 4.1., 4.2., etc.)
    const numberedSectionMatch = trimmed.match(/^(\d+\.\d*\.?)\s+(.+)$/);
    if (numberedSectionMatch) {
      const [, number, content] = numberedSectionMatch;
      const children: TextRun[] = [
        new TextRun({
          text: number + ' ',
          bold: true,
        }),
      ];
      
      // Handle bold text in content (e.g., "1. **Guaranty**")
      if (content.includes('**')) {
        const parts = content.split(/(\*\*[^*]+\*\*)/g);
        parts.forEach(part => {
          if (part.startsWith('**') && part.endsWith('**')) {
            children.push(new TextRun({
              text: part.replace(/\*\*/g, ''),
              bold: true,
            }));
          } else if (part.trim()) {
            children.push(new TextRun({ text: part }));
          }
        });
      } else {
        children.push(new TextRun({ text: content }));
      }
      
      paragraphs.push(
        new Paragraph({
          children: children,
          spacing: {
            before: 120,
            after: 80,
          },
          alignment: AlignmentType.LEFT,
        })
      );
      continue;
    }

    // Check for bullet points (•)
    if (trimmed.startsWith('•') || trimmed.startsWith('-')) {
      paragraphs.push(
        new Paragraph({
          text: trimmed,
          spacing: {
            before: 60,
            after: 60,
          },
          alignment: AlignmentType.LEFT,
          indent: {
            left: 360, // 0.25 inch indent for bullets
          },
        })
      );
      continue;
    }

    // Check for bold text markers (**text**)
    if (trimmed.includes('**')) {
      const parts = trimmed.split(/(\*\*[^*]+\*\*)/g);
      const children = parts.map(part => {
        if (part.startsWith('**') && part.endsWith('**')) {
          return new TextRun({
            text: part.replace(/\*\*/g, ''),
            bold: true,
          });
        }
        return new TextRun({ text: part });
      });
      
      paragraphs.push(
        new Paragraph({
          children: children,
          spacing: {
            before: 80,
            after: 80,
          },
          alignment: AlignmentType.LEFT,
        })
      );
      continue;
    }

    // Check for bold text patterns (like "TOTAL AMOUNT DUE:", "IN WITNESS WHEREOF", etc.)
    const boldPatterns = [
      'TOTAL AMOUNT DUE:',
      'IN WITNESS WHEREOF',
      'ACKNOWLEDGMENT',
      'BEFORE ME',
      'WITNESS MY HAND',
      'Doc. No.',
      'Page No.',
      'Book No.',
      'Series of',
      'Notary Public',
      'PTR No.',
      'IBP No.',
      'Commission No.',
      'Until:',
      'Name:',
      'Creditor / Lender',
      'Guarantor',
      'Lender',
      'Borrower',
    ];
    
    const isBoldPattern = boldPatterns.some(pattern => trimmed.includes(pattern));
    if (isBoldPattern) {
      paragraphs.push(
        new Paragraph({
          text: trimmed,
          spacing: {
            before: 120,
            after: 80,
          },
          alignment: AlignmentType.LEFT,
        })
      );
      continue;
    }

    // Check if line contains signature lines (underscores)
    if (trimmed.includes('________________') || (trimmed.includes('___') && trimmed.length < 50)) {
      paragraphs.push(
        new Paragraph({
          text: trimmed,
          alignment: AlignmentType.CENTER,
          spacing: {
            before: 240,
            after: 120,
          },
        })
      );
      continue;
    }

    // Check for "- and -" separator (centered)
    if (trimmed === '- and -') {
      paragraphs.push(
        new Paragraph({
          text: trimmed,
          alignment: AlignmentType.CENTER,
          spacing: {
            before: 120,
            after: 120,
          },
        })
      );
      continue;
    }

    // Check for "WHEREAS," or "NOW, THEREFORE," (left aligned, but with special spacing)
    if (trimmed.startsWith('WHEREAS,') || trimmed.startsWith('NOW, THEREFORE,')) {
      paragraphs.push(
        new Paragraph({
          text: trimmed,
          spacing: {
            before: 120,
            after: 120,
          },
          alignment: AlignmentType.LEFT,
        })
      );
      continue;
    }

    // Regular paragraph - preserve original formatting
    paragraphs.push(
      new Paragraph({
        text: trimmed,
        spacing: {
          before: 80, // 4pt before
          after: 80, // 4pt after
        },
        alignment: AlignmentType.LEFT, // Left align for regular text
      })
    );
  }

  return paragraphs;
}

/**
 * Generate a Word document (.docx) from text content
 */
export async function generateWordDocument(
  options: WordDocumentOptions
): Promise<string> {
  const { content, fileName, outputDir } = options;

  // Ensure output directory exists
  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
  }

  // Convert text to paragraphs
  const paragraphs = textToParagraphs(content);

  // Create Word document with proper formatting
  const doc = new Document({
    styles: {
      default: {
        document: {
          run: {
            font: "Times New Roman",
            size: 24, // 12pt = 24 half-points
          },
        },
      },
    },
    sections: [
      {
        properties: {
          page: {
            margin: {
              top: 1440, // 1 inch = 1440 twips
              right: 1440,
              bottom: 1440,
              left: 1440,
            },
          },
        },
        children: [
          // Don't add title - it's already in the template content
          ...paragraphs,
        ],
      },
    ],
  });

  // Generate document buffer
  const buffer = await Packer.toBuffer(doc);

  // Save to file
  const filePath = path.join(outputDir, fileName);
  fs.writeFileSync(filePath, buffer);

  return filePath;
}

/**
 * Generate Word document and return file URL
 */
export async function generateWordDocumentWithUrl(
  options: WordDocumentOptions,
  baseUrl: string
): Promise<{ filePath: string; fileUrl: string }> {
  const filePath = await generateWordDocument(options);
  
  // Generate relative URL from uploads directory
  // If filePath is: uploads/loan-documents/123/agreement.docx
  // URL should be: /uploads/loan-documents/123/agreement.docx
  const uploadsIndex = filePath.indexOf('uploads');
  const relativePath = uploadsIndex !== -1 
    ? filePath.substring(uploadsIndex)
    : path.basename(filePath);
  
  const fileUrl = `${baseUrl}/${relativePath.replace(/\\/g, '/')}`;

  return { filePath, fileUrl };
}

