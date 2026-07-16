<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'group_name',
        'description',
        'is_active',
        'member_count',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'member_count' => 'integer',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function users()
    {
        return $this->morphedByMany(User::class, 'groupable', 'groupables', 'group_id', 'groupable_id')
                    ->withTimestamps();
    }

    public function crewMembers()
    {
        return $this->morphedByMany(CrewMember::class, 'groupable', 'groupables', 'group_id', 'groupable_id')
                    ->withTimestamps();
    }

    public function contracts()
    {
        return $this->morphedByMany(Contract::class, 'groupable', 'groupables', 'group_id', 'groupable_id')
                    ->withTimestamps();
    }

    public function contacts()
    {
        return $this->morphedByMany(Contact::class, 'groupable', 'groupables', 'group_id', 'groupable_id')
                    ->withTimestamps();
    }

    public function members()
    {
        return $this->hasMany(Groupable::class, 'group_id');
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('group_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // -------------------------------------------------------
    // Methods
    // -------------------------------------------------------

    public function updateMemberCount(): void
    {
        $this->update([
            'member_count' => $this->members()->count(),
        ]);
    }

    public function addMember($member): void
    {
        if ($member instanceof User) {
            $this->users()->attach($member->id);
        } elseif ($member instanceof CrewMember) {
            $this->crewMembers()->attach($member->id);
        } elseif ($member instanceof Contract) {
            $this->contracts()->attach($member->id);
        } elseif ($member instanceof Contact) {
            $this->contacts()->attach($member->id);
        }

        $this->increment('member_count');
    }

    public function removeMember($member): void
    {
        if ($member instanceof User) {
            $this->users()->detach($member->id);
        } elseif ($member instanceof CrewMember) {
            $this->crewMembers()->detach($member->id);
        } elseif ($member instanceof Contract) {
            $this->contracts()->detach($member->id);
        } elseif ($member instanceof Contact) {
            $this->contacts()->detach($member->id);
        }

        $this->decrement('member_count');
    }

    public function hasMember($member): bool
    {
        if ($member instanceof User) {
            return $this->users()->where('users.id', $member->id)->exists();
        } elseif ($member instanceof CrewMember) {
            return $this->crewMembers()->where('crew_members.id', $member->id)->exists();
        } elseif ($member instanceof Contract) {
            return $this->contracts()->where('contracts.id', $member->id)->exists();
        } elseif ($member instanceof Contact) {
            return $this->contacts()->where('contacts.id', $member->id)->exists();
        }

        return false;
    }

    public function addMemberSafely($member): bool
    {
        if ($this->hasMember($member)) {
            return false;
        }

        $this->addMember($member);
        return true;
    }

    public function getAllMembers(): \Illuminate\Support\Collection
    {
        $users = collect($this->users()->get()->map(fn ($u) => [
            'id'        => $u->id,
            'name'      => $u->name,
            'type'      => 'user',
            'email'     => $u->email,
            'phone'     => $u->phone ?? null,
            'is_active' => $u->is_active,
            'joined_at' => $u->pivot->created_at,
        ])->all());

        $crews = collect($this->crewMembers()->get()->map(fn ($c) => [
            'id'        => $c->id,
            'name'      => $c->name,
            'type'      => 'crew',
            'email'     => null,
            'phone'     => $c->phone,
            'is_active' => $c->is_active,
            'joined_at' => $c->pivot->created_at,
        ])->all());

        $contracts = collect($this->contracts()->get()->map(fn ($c) => [
            'id'        => $c->id,
            'name'      => $c->nama_kontraktor,
            'type'      => 'contract',
            'email'     => $c->alamat_email ?? null,
            'phone'     => $c->no_tlp_kantor ?? null,
            'sap_no'    => $c->sap_no ?? null,
            'is_active' => true,
            'joined_at' => $c->pivot->created_at,
        ])->all());

        $contacts = collect($this->contacts()->get()->map(fn ($c) => [
            'id'        => $c->id,
            'name'      => $c->name,
            'type'      => 'contact',
            'email'     => null,
            'phone'     => $c->whatsapp_number,
            'position'  => $c->position ?? null,
            'is_active' => $c->is_active,
            'joined_at' => $c->pivot->created_at,
        ])->all());

        return $users->merge($crews)->merge($contracts)->merge($contacts)
                     ->sortByDesc('joined_at')
                     ->values();
    }
}
