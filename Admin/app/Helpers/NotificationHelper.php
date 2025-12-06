<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\Loan;

class NotificationHelper
{
    /**
     * Get status display name
     */
    private static function getStatusDisplay(string $status): string
    {
        $statusMap = [
            Loan::ST_NEW => 'New Application',
            Loan::ST_REVIEW => 'Under Review',
            Loan::ST_APPROVED => 'Approved',
            Loan::ST_FOR_RELEASE => 'For Release',
            Loan::ST_DISBURSED => 'Disbursed',
            Loan::ST_CLOSED => 'Closed',
            Loan::ST_REJECTED => 'Rejected',
            Loan::ST_RESTRUCTURED => 'Restructured',
        ];

        return $statusMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * Create notification when loan status changes
     */
    public static function notifyLoanStatusChange(Loan $loan, string $oldStatus, string $newStatus): void
    {
        if (!$loan->borrower_id) {
            return; // No borrower to notify
        }

        $statusDisplay = self::getStatusDisplay($newStatus);
        $reference = $loan->reference;

        // Determine notification type and message based on status
        $type = Notification::TYPE_LOAN_STATUS_CHANGE;
        $title = "Loan Status Updated";
        $message = "Your loan application {$reference} status has been changed to: {$statusDisplay}.";

        // Customize message for specific statuses
        switch ($newStatus) {
            case Loan::ST_APPROVED:
                $type = Notification::TYPE_APPROVAL;
                $title = "Loan Approved! 🎉";
                $message = "Congratulations! Your loan application {$reference} has been approved. It will be processed for release soon.";
                break;

            case Loan::ST_DISBURSED:
                $type = Notification::TYPE_APPROVAL;
                $title = "Loan Disbursed! 💰";
                $releaseDate = $loan->release_date ? date('F d, Y', strtotime($loan->release_date)) : 'today';
                $amount = number_format((float) $loan->principal_amount, 2);
                $message = "Your loan {$reference} has been disbursed on {$releaseDate}. Amount: ₱{$amount}. Please check your account and start making payments according to your schedule.";
                break;

            case Loan::ST_REJECTED:
                $title = "Loan Application Rejected";
                $message = "We regret to inform you that your loan application {$reference} has been rejected. Please contact us for more information.";
                break;

            case Loan::ST_CLOSED:
                $title = "Loan Closed";
                $message = "Your loan {$reference} has been closed. Thank you for your business!";
                break;

            case Loan::ST_FOR_RELEASE:
                $title = "Loan Ready for Release";
                $message = "Your loan application {$reference} is ready for release. It will be disbursed soon.";
                break;

            case Loan::ST_REVIEW:
                $title = "Loan Under Review";
                $message = "Your loan application {$reference} is now under review. We will notify you once a decision has been made.";
                break;
        }

        Notification::createForBorrower(
            $loan->borrower_id,
            $type,
            $title,
            $message,
            $loan->id
        );
    }

    /**
     * Create notification when loan is created
     */
    public static function notifyLoanCreated(Loan $loan): void
    {
        if (!$loan->borrower_id) {
            return;
        }

        $reference = $loan->reference;
        $amount = number_format((float) $loan->principal_amount, 2);

        Notification::createForBorrower(
            $loan->borrower_id,
            Notification::TYPE_INFO,
            "Loan Application Submitted",
            "Your loan application {$reference} for ₱{$amount} has been submitted successfully. We will review it and notify you of the status.",
            $loan->id
        );
    }
}

