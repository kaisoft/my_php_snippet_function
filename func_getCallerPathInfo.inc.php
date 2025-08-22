<?php
/**
 * Get detailed path information of the file that called this function.
 *
 * This function returns an associative array containing various details about the
 * file which invoked this function, such as dirname, basename, filename (without extension),
 * extension, full path info, and optionally server environment info.
 * 
 * It uses debug_backtrace() to determine the caller file, making it useful even when called
 * from an included or required file.
 *
 * @param bool $includeServerInfo Optional. Set to true to include server-related info 
 *                                (e.g., DOCUMENT_ROOT, SERVER_NAME). Default is false.
 * 
 * @return array|null Returns an associative array with path details and optionally server info.
 *                    Returns null if caller file is not found or invalid.
 *
 * @example
 * <code>
 * print_r(getCallerPathInfo(true));
 * </code>
 */
function getCallerPathInfo($includeServerInfo = false) {
    // Get the debug backtrace to find caller info
    $trace = debug_backtrace();

    // The caller is the second element in the backtrace array
    $caller = isset($trace[1]) ? $trace[11] : null;

    if (!$caller || !isset($caller['file'])) {
        // No caller info found, return null
        return null;
    }

    // Resolve the realpath of the caller file to avoid symbolic link issues
    $callerFile = realpath($caller['file']);

    // Validate that the resolved path exists and is a file
    if ($callerFile === false || !is_file($callerFile)) {
        // Invalid file path, return null to indicate failure
        return null;
    }

    // Initialize the result array to hold path details
    $result = array();

    // Directory part of the file path
    $dirname = dirname($callerFile);
    $result['dirname'] = $dirname;

    // Basename (file name with extension)
    $basename = basename($callerFile);
    $result['basename'] = $basename;

    // File extension
    $extension = pathinfo($callerFile, PATHINFO_EXTENSION);
    $result['extension'] = $extension;

    // Filename without extension
    if ($extension !== '') {
        $filename = substr($basename, 0, strrpos($basename, '.'));
    } else {
        $filename = $basename;
    }
    $result['filename'] = $filename;

    // Full path info as returned by pathinfo
    $result['pathinfo'] = pathinfo($callerFile);

    // Optionally include server environment information
    if ($includeServerInfo) {
        $result['server'] = array(
            'document_root' => isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '',
            'server_name'   => isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '',
            'server_addr'   => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '',
            'remote_addr'   => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
        );
    } else {
        // If not included, set to null to avoid accidental info leakage
        $result['server'] = null;
    }

    // Calculate folders as an array representing folder hierarchy relative to document root
    $folders = array();
    if ($includeServerInfo && !empty($result['server']['document_root'])) {
        $docRoot = realpath($result['server']['document_root']);
        $realDir = realpath($dirname);
        if ($docRoot !== false && $realDir !== false && strpos($realDir, $docRoot) === 0) {
            // Get relative path by trimming document root from filename path
            $relativePath = substr($realDir, strlen($docRoot));
            $relativePath = trim($relativePath, DIRECTORY_SEPARATOR);
            if ($relativePath != '') {
                $folders = explode(DIRECTORY_SEPARATOR, $relativePath);
            }
        }
    }
    $result['folders'] = $folders;

    // Return the assembled array of path and optionally server info
    return $result;
}
?>
