<tr data-account-type="{{ $account->account_type }}"
    data-parent-id="{{ $account->parent_id }}"
    data-account-id="{{ $account->id }}"
    class="{{ $account->parent_id ? 'child-row' : 'parent-row' }}">
    <td>
        <span class="account-code badge bg-secondary">{{ $account->account_code }}</span>
    </td>
    <td>
        <div class="d-flex align-items-center">
            @if($account->hasChildren())
                <button type="button" class="btn btn-sm p-0 me-2 toggle-children" data-parent-id="{{ $account->id }}">
                    <i class="fas fa-plus-circle text-primary"></i>
                </button>
            @endif

            <div style="margin-left: {{ $level * 20 }}px;">
                <span class="account-name">
                    @if(!$account->allow_posting)
                        <strong>{{ $account->account_name }}</strong>
                        <span class="badge bg-dark ms-1">Header</span>
                    @else
                        {{ $account->account_name }}
                    @endif
                </span>
                @if($account->description)
                    <br><small class="text-muted">{{ $account->description }}</small>
                @endif
            </div>
        </div>
    </td>
    <td>
        <span class="badge bg-{{ $account->account_type == 'asset' ? 'primary' :
                                ($account->account_type == 'liability' ? 'danger' :
                                ($account->account_type == 'equity' ? 'warning' :
                                ($account->account_type == 'revenue' ? 'success' : 'info'))) }}">
            {{ ucfirst($account->account_type) }}
        </span>
    </td>
    <td>
        <small>{{ ucfirst(str_replace('_', ' ', $account->account_subtype)) }}</small>
    </td>
    <td>
        <span class="badge bg-{{ $account->normal_balance == 'debit' ? 'primary' : 'success' }}">
            {{ ucfirst($account->normal_balance) }}
        </span>
    </td>
    <td>
        @if($account->is_active)
            <span class="badge bg-success">Active</span>
        @else
            <span class="badge bg-secondary">Inactive</span>
        @endif
    </td>
    <td>
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('chart-of-accounts.show', $account) }}"
               class="btn btn-outline-primary" title="View Details">
                <i class="fas fa-eye"></i>
            </a>
            <a href="{{ route('chart-of-accounts.edit', $account) }}"
               class="btn btn-outline-warning" title="Edit">
                <i class="fas fa-edit"></i>
            </a>
        </div>
    </td>
</tr>

@if($account->children->count() > 0)
    @foreach($account->children as $child)
        @include('chart-of-accounts.partials.account-row', ['account' => $child, 'level' => $level + 1])
    @endforeach
@endif
