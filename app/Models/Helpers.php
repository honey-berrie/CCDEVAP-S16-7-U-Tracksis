<?php

namespace App\Models;

use App\Core\Model;


class Helpers extends Model
{

    public static function initialsOf($first, $last) {
        $f = $first ? substr($first, 0, 1) : '';
        $l = $last ? substr($last, 0, 1)  : '';

        return strtoupper(($f . $l) ?: 'SY');
    }

    public static function statusLabel($status) {
        return [
            'pending' => 'Pending',
            'in-progress' => 'In Progress',
            'in-review' => 'In Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ][$status];
    }

    public static function statusClass($status) {
        return [
            'pending' => 'badge-progress',
            'in-progress' => 'badge-progress',
            'in-review' => 'badge-review',
            'approved' => 'badge-approved',
            'rejected' => 'badge-rejected',
        ][$status] ?? 'badge-pending';
    }

    public static function timeAgo( $ts) {
        $diff = time() - $ts;

        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
        if ($diff < 604800) return floor($diff / 86400) . ' day' . (floor($diff / 86400) > 1 ? 's' : '') . ' ago';

        return date('M j, Y', $ts);
    }

    public static function iconForType($type){
        return [
            'submission' => 'cloud-upload-fill',
            'submission_uploaded' => 'cloud-upload-fill',
            'submission_reviewed' => 'check-circle-fill',
            'milestone_approved' => 'flag-fill',
            'milestone_updated' => 'flag-fill',
            'feedback_added' => 'chat-left-text-fill',
            'consultation_requested' => 'chat-dots-fill',
            'consultation_approved' => 'calendar-check',
            'announcement_posted' => 'megaphone-fill'
        ][$type] ?? 'activity';
    }

}

?>