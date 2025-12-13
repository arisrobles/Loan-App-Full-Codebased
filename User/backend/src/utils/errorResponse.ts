import { Response } from 'express';
import { ZodError } from 'zod';

/**
 * Standardized error response format
 * This ensures consistent error structure across the API
 */
export interface StandardErrorResponse {
  success: false;
  message: string;
  errors?: Array<{
    field: string;
    message: string;
    code?: string;
  }>;
  existingLoan?: any;
  missingFields?: Record<string, boolean>;
}

/**
 * Send standardized error response
 */
export const sendErrorResponse = (
  res: Response,
  statusCode: number,
  message: string,
  errors?: Array<{ field: string; message: string; code?: string }>,
  additionalData?: Record<string, any>
): Response => {
  const response: StandardErrorResponse = {
    success: false,
    message,
    ...(errors && errors.length > 0 && { errors }),
    ...additionalData,
  };

  return res.status(statusCode).json(response);
};

/**
 * Convert Zod validation errors to standardized format
 */
export const formatZodErrors = (zodError: ZodError): Array<{ field: string; message: string; code: string }> => {
  return zodError.errors.map((error) => ({
    field: error.path.join('.'),
    message: error.message,
    code: error.code,
  }));
};

/**
 * Send validation error response from Zod
 */
export const sendValidationError = (res: Response, zodError: ZodError): Response => {
  const errors = formatZodErrors(zodError);
  return sendErrorResponse(res, 400, 'Validation error', errors);
};

