<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NgoInviteLink;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\AuthController;
use App\Models\NgoStaff;

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
            'link' => url('/register?token=' . $link->token)
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

}
