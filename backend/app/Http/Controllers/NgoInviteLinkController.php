<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NgoInviteLink;
use Illuminate\Support\Str;


class NgoInviteLinkController extends Controller
{
    //
    public function index($ngoId)
    {
        return NgoInviteLink::where('ngo_id', $ngoId)->get();
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'ngo_id' => 'required|exists:ngos,id',
            'privilege_role' => 'required|in:ngo_admin,manager,general_staff',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        $link = NgoInviteLink::create([
            'ngo_id' => $fields['ngo_id'],
            'privilege_role' => $fields['privilege_role'],
            'token' => Str::random(32),
            'usage_limit' => $fields['usage_limit'],
            'is_primary' => false,
        ]);

        return response()->json([
            'message' => 'Invite link created',
            'link' => url(path: '/register?token=' . $link->token)
        ]);
    }

    public function markInviteUsed(NgoInviteLink $invite): void
    {
        $invite->increment('used_count');

        if ($invite->usage_limit && $invite->used_count >= $invite->usage_limit) {
            $invite->active = false;
        }

        $invite->save();
    }

    public function activeLinks(Request $request)
    {
        $user = $request->user();
        $ngoStaff = $user->ngoStaff;
        $ngoId = $ngoStaff ? $ngoStaff->ngo_id : null;
        if (!$ngoId) return response()->json(['error' => 'No ngo_id found for user', 'user' => $user], 400);
        $links = NgoInviteLink::where('ngo_id', $ngoId)
            ->where('is_primary', false)
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'link' => url('api/auth/redirect?token=' . $l->token),
                    'expiry_date' => $l->expiry_date,
                    'usage_limit' => $l->usage_limit,
                    'used_count' => $l->used_count,
                    'active' => $l->active,
                ];
            });
        return response()->json($links);
    }
}
