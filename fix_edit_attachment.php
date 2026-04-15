<?php
/**
 * Fix for the attachment replacement bug in edit_transaction.php
 * 
 * Problem: When both "remove attachment" checkbox AND file upload are used together,
 * the newly uploaded file gets deleted.
 * 
 * Root cause: File upload happens first (lines 50-76), then remove attachment logic
 * (lines 77-88). When remove is checked, it deletes the CURRENT attachment which at
 * that point is the newly uploaded file.
 * 
 * Fix: When remove_attachment is checked, skip the file upload processing entirely.
 * The remove flag should take precedence - user explicitly wants to remove, not replace.
 */

$file_content = file_get_contents('actions/edit_transaction.php');

// Find the problematic section
$old_code = <<<'OLD'
            // Handle file upload
            $attachment_path = null;
            if (isset(\$_FILES['attachment']) && \$_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                \$file = \$_FILES['attachment'];
                \$allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                \$allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];

                \$file_extension = strtolower(pathinfo(\$file['name'], PATHINFO_EXTENSION));

                if (!in_array(\$file['type'], \$allowed_types) || !in_array(\$file_extension, \$allowed_extensions)) {
                    \$response['message'] = 'Please upload only PDF or DOC/DOCX files.';
                    echo json_encode(\$response);
                    exit();
                }

                if (\$file['size'] > 5 * 1024 * 1024) {
                    \$response['message'] = 'File size must be less than 5MB.';
                    echo json_encode(\$response);
                    exit();
                }

                \$upload_dir = 'uploads/';
                if (!is_dir(\$upload_dir)) {
                    mkdir(\$upload_dir, 0755, true);
                }

                \$filename = 'tx_' . date('YmdHis') . '_' . uniqid() . '.' . \$file_extension;
                \$filepath = \$upload_dir . \$filename;

                if (move_uploaded_file(\$file['tmp_name'], \$filepath)) {
                    // Delete old attachment if exists
                    if (\$transaction->getTransactionById(\$transaction_id)['attachment_path'] &&
                        file_exists(\$transaction->getTransactionById(\$transaction_id)['attachment_path'])) {
                        unlink(\$transaction->getTransactionById(\$transaction_id)['attachment_path']);
                    }
                    \$attachment_path = \$filepath;
                } else {
                    \$response['message'] = 'Failed to upload file. Please try again.';
                    echo json_encode(\$response);
                    exit();
                }
            } elseif (\$remove_attachment) {
                // Remove existing attachment
                \$old_tx = \$transaction->getTransactionById(\$transaction_id);
                if (\$old_tx['attachment_path'] && file_exists(\$old_tx['attachment_path'])) {
                    unlink(\$old_tx['attachment_path']);
                }
                \$attachment_path = null;
            }
OLD;

// New code: remove flag takes precedence, skip upload processing
$new_code = <<<'NEW'
            // Handle file upload or removal - remove takes precedence
            $attachment_path = null;
            if ($remove_attachment) {
                // Remove existing attachment, skip any file upload
                $old_tx = $transaction->getTransactionById($transaction_id);
                if ($old_tx['attachment_path'] && file_exists($old_tx['attachment_path'])) {
                    unlink($old_tx['attachment_path']);
                }
                $attachment_path = null;
            } elseif (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['attachment'];
                $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];

                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($file['type'], $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
                    $response['message'] = 'Please upload only PDF or DOC/DOCX files.';
                    echo json_encode($response);
                    exit();
                }

                if ($file['size'] > 5 * 1024 * 1024) {
                    $response['message'] = 'File size must be less than 5MB.';
                    echo json_encode($response);
                    exit();
                }

                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $filename = 'tx_' . date('YmdHis') . '_' . uniqid() . '.' . $file_extension;
                $filepath = $upload_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Delete old attachment if exists
                    if ($transaction->getTransactionById($transaction_id)['attachment_path'] &&
                        file_exists($transaction->getTransactionById($transaction_id)['attachment_path'])) {
                        unlink($transaction->getTransactionById($transaction_id)['attachment_path']);
                    }
                    $attachment_path = $filepath;
                } else {
                    $response['message'] = 'Failed to upload file. Please try again.';
                    echo json_encode($response);
                    exit();
                }
            }
NEW;

if (strpos($file_content, $old_code) !== false) {
    $new_content = str_replace($old_code, $new_code, $file_content);
    file_put_contents('actions/edit_transaction.php', $new_content);
    echo "Fix applied successfully!\n";
    echo "The bug: When both 'remove attachment' and file upload were used together,\n";
    echo "the newly uploaded file would be deleted immediately.\n";
    echo "\nFix: Added 'elseif' so remove_attachment skips upload processing entirely.\n";
} else {
    echo "Could not find the code to replace. Manual fix needed.\n";
}
