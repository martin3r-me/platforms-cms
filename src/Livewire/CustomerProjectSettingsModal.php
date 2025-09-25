<?php

namespace Platform\Cms\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Platform\Cms\Models\CmsProject;
use Platform\Cms\Models\CmsCustomerProject;
use Platform\Core\Contracts\CrmCompanyResolverInterface;
use Platform\Core\Contracts\CrmCompanyOptionsProviderInterface;

class CustomerProjectSettingsModal extends Component
{
    public $modalShow = false;
    public $project;
    public $companyId = null;
    public $companyDisplay = null;
    public $companyOptions = [];
    public $companySearch = '';
    public $customerModel = null; // 'crm.companies' | 'crm.contacts'
    public $customerId = null;
    public $customerDisplay = null;

    #[On('open-modal-cms-customer-project')]
    public function openModal($projectId)
    {
        $this->project = CmsProject::with('customerProject')->findOrFail($projectId);
        $this->companyId = $this->project->customerProject?->company_id;
        $this->customerModel = $this->project->customerProject?->customer_model;
        $this->customerId = $this->project->customerProject?->customer_id;
        $this->resolveCompanyDisplay();
        $this->resolveCustomerDisplay();
        $this->loadCompanyOptions('');
        $this->modalShow = true;
    }

    public function closeModal()
    {
        $this->modalShow = false;
    }

    public function render()
    {
        return view('cms::livewire.customer-project-settings-modal')->layout('platform::layouts.app');
    }

    public function updatedCompanyId($value)
    {
        $this->resolveCompanyDisplay();
    }

    public function updatedCompanySearch($value)
    {
        $this->loadCompanyOptions($this->companySearch);
    }

    private function resolveCustomerDisplay(): void
    {
        if (!$this->customerModel || !$this->customerId) {
            $this->customerDisplay = null;
            return;
        }
        if ($this->customerModel === 'crm.companies') {
            /** @var CrmCompanyResolverInterface $resolver */
            $resolver = app(CrmCompanyResolverInterface::class);
            $this->customerDisplay = $resolver->displayName((int)$this->customerId);
        } elseif ($this->customerModel === 'crm.contacts') {
            /** @var \Platform\Core\Contracts\CrmContactResolverInterface $resolver */
            $resolver = app(\Platform\Core\Contracts\CrmContactResolverInterface::class);
            $this->customerDisplay = $resolver->displayName((int)$this->customerId);
        } else {
            $this->customerDisplay = null;
        }
    }

    private function resolveCompanyDisplay(): void
    {
        /** @var CrmCompanyResolverInterface $resolver */
        $resolver = app(CrmCompanyResolverInterface::class);
        $this->companyDisplay = $resolver->displayName($this->companyId ? (int)$this->companyId : null);
    }

    private function loadCompanyOptions(?string $q = null): void
    {
        /** @var CrmCompanyOptionsProviderInterface $provider */
        $provider = app(CrmCompanyOptionsProviderInterface::class);
        $this->companyOptions = $provider->options($q, 50);

        if ($this->companyId && !collect($this->companyOptions)->contains(fn($o) => (string)($o['value'] ?? null) === (string)$this->companyId)) {
            /** @var CrmCompanyResolverInterface $resolver */
            $resolver = app(CrmCompanyResolverInterface::class);
            $label = $resolver->displayName((int)$this->companyId) ?? ('#'.$this->companyId);
            array_unshift($this->companyOptions, [
                'value' => (int)$this->companyId,
                'label' => $label,
            ]);
        }
    }

    public function saveCompany()
    {
        if (! $this->project) return;

        CmsCustomerProject::updateOrCreate(
            ['project_id' => $this->project->id],
            [
                'project_id' => $this->project->id,
                'team_id' => Auth::user()->currentTeam->id,
                'user_id' => Auth::id(),
                'company_id' => $this->companyId ? (int)$this->companyId : null, // legacy fallback
                'customer_model' => $this->customerModel,
                'customer_id' => $this->customerId ? (int)$this->customerId : null,
                // Tool/URL nicht mehr verwendet – alles via Contracts
            ]
        );

        $this->project->refresh();
        $this->resolveCompanyDisplay();
        $this->resolveCustomerDisplay();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Kundenfirma gespeichert',
        ]);
    }
}

 

