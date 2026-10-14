<?php

namespace App\Support\Settings;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Normalised view of config/settings.php for the Admin › Pengaturan screens:
 * groups, sections, fields (with options, rules and input names) and who may
 * view or change each group.
 *
 * Form inputs live under the "s" array using the dotted key as path, so
 * "appearance.theme" is posted as s[appearance][theme] and validated as
 * "s.appearance.theme". Image removal flags use the same path under "remove".
 */
class SettingsRegistry
{
    /** Root input name of the settings form. */
    public const INPUT = 's';

    /** Root input name of the "remove this image" flags. */
    public const REMOVE = 'remove';

    /** Roles that may open Admin › Pengaturan (and edit groups without edit_roles). */
    public const ADMIN_ROLES = ['admin', 'superadmin'];

    /** Field types rendered as text inputs. */
    public const TEXT_TYPES = ['text', 'email', 'url', 'tel'];

    /** Rules for repeater sub-fields that have no item_rules. */
    private const DEFAULT_ITEM_RULES = ['nullable', 'string', 'max:1000'];

    /** @var array<string, array<string, array<string, mixed>>> memo of normalised fields per group */
    private array $fieldCache = [];

    public function __construct(private SettingsManager $settings) {}

    /**
     * All groups in registry order with their metadata.
     *
     * @return array<string, array{key:string, label:string, icon:string, description:?string, edit_roles:array<int,string>, confirm_password:bool, sections:array<string,string>}>
     */
    public function groups(): array
    {
        $groups = [];

        foreach ($this->settings->groups() as $key => $group) {
            $groups[$key] = [
                'key' => (string) $key,
                'label' => (string) ($group['label'] ?? Str::headline($key)),
                'icon' => (string) ($group['icon'] ?? 'cog-6-tooth'),
                'description' => $group['description'] ?? null,
                'edit_roles' => array_values(array_unique(array_merge(
                    ['superadmin'],
                    (array) ($group['edit_roles'] ?? self::ADMIN_ROLES),
                ))),
                'confirm_password' => (bool) ($group['confirm_password'] ?? false),
                'sections' => (array) ($group['sections'] ?? []),
            ];
        }

        return $groups;
    }

    /**
     * Metadata of one group, or null when unknown.
     *
     * @return array<string, mixed>|null
     */
    public function group(string $group): ?array
    {
        return $this->groups()[$group] ?? null;
    }

    /**
     * Whether the group exists.
     */
    public function hasGroup(string $group): bool
    {
        return array_key_exists($group, $this->settings->groups());
    }

    /**
     * Normalised fields of a group in registry order, keyed by setting key.
     *
     * @return array<string, array<string, mixed>>
     */
    public function fields(string $group): array
    {
        if (isset($this->fieldCache[$group])) {
            return $this->fieldCache[$group];
        }

        $fields = [];
        foreach ($this->settings->definitions() as $key => $definition) {
            if (is_array($definition) && ($definition['group'] ?? Str::before($key, '.')) === $group) {
                $fields[$key] = $this->normalise((string) $key, $definition);
            }
        }

        return $this->fieldCache[$group] = $fields;
    }

    /**
     * Normalised field definition for a key, or null when unknown.
     *
     * @return array<string, mixed>|null
     */
    public function field(string $key): ?array
    {
        $definition = $this->settings->definition($key);

        return $definition === null ? null : $this->normalise($key, $definition);
    }

    /**
     * Fields of a group bundled per section (order of first appearance).
     *
     * @return array<int, array{title:?string, description:?string, fields:array<string, array<string, mixed>>}>
     */
    public function sections(string $group): array
    {
        $descriptions = $this->group($group)['sections'] ?? [];
        $sections = [];

        foreach ($this->fields($group) as $key => $field) {
            $title = $field['section'];
            $id = $title ?? '';

            $sections[$id] ??= [
                'title' => $title,
                'description' => $title !== null ? ($descriptions[$title] ?? null) : null,
                'fields' => [],
            ];
            $sections[$id]['fields'][$key] = $field;
        }

        return array_values($sections);
    }

    /**
     * Whether the user may open Admin › Pengaturan.
     */
    public function canView(?User $user): bool
    {
        return $user !== null && $user->hasAnyRole(self::ADMIN_ROLES);
    }

    /**
     * Whether the user may change the settings of a group.
     */
    public function canEdit(?User $user, string $group): bool
    {
        $meta = $this->group($group);

        return $meta !== null && $user !== null && $user->hasAnyRole($meta['edit_roles']);
    }

    /**
     * Whether editors must re-confirm their password before changing the group.
     */
    public function needsPasswordConfirmation(string $group): bool
    {
        return (bool) ($this->group($group)['confirm_password'] ?? false);
    }

    /**
     * Validation rules for every field of a group (keys under the "s" input).
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(string $group): array
    {
        $rules = [];

        foreach ($this->fields($group) as $field) {
            $path = $field['path'];

            switch ($field['type']) {
                case 'multiselect':
                    $rules[$path] = $this->withRule($field['rules'], 'array');
                    $rules[$path.'.*'] = array_merge(['string'], $field['item_rules'] ?: [
                        'in:'.implode(',', array_keys($field['options'])),
                    ]);
                    break;

                case 'repeater':
                    $rules[$path] = $this->withRule($field['rules'], 'array');
                    $rules[$path.'.*'] = ['array'];
                    foreach ($field['fields'] as $name => $sub) {
                        $rules[$path.'.*.'.$name] = $field['item_rules'][$name] ?? self::DEFAULT_ITEM_RULES;
                    }
                    break;

                case 'list':
                    $rules[$path] = $this->withRule($field['rules'], 'array');
                    $rules[$path.'.*'] = $field['item_rules'] ?: ['string', 'max:200'];
                    break;

                default:
                    $rules[$path] = $field['rules'];
            }

            if ($field['type'] === 'image') {
                $rules[self::REMOVE.'.'.$field['key']] = ['nullable', 'boolean'];
            }
        }

        return $rules;
    }

    /**
     * Human attribute names for validation messages (Indonesian labels).
     *
     * @return array<string, string>
     */
    public function attributes(string $group): array
    {
        $attributes = [];

        foreach ($this->fields($group) as $field) {
            $attributes[$field['path']] = $field['label'];
            $attributes[$field['path'].'.*'] = $field['label'];

            foreach ($field['fields'] as $name => $sub) {
                $attributes[$field['path'].'.*.'.$name] = $sub['label'];
            }
        }

        return $attributes;
    }

    /**
     * Input name of a key, e.g. "appearance.theme" → "s[appearance][theme]".
     */
    public static function inputName(string $key, string $root = self::INPUT): string
    {
        return $root.'['.implode('][', explode('.', $key)).']';
    }

    /**
     * HTML id of a key, e.g. "appearance.theme" → "s_appearance_theme".
     */
    public static function inputId(string $key): string
    {
        return self::INPUT.'_'.str_replace(['.', '-'], '_', $key);
    }

    /**
     * Turn one config entry into the shape the views and the form handler use.
     *
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private function normalise(string $key, array $definition): array
    {
        $type = (string) ($definition['type'] ?? 'text');
        $rules = $this->ruleList($definition['rules'] ?? []);

        $options = [];
        foreach ((array) ($definition['options'] ?? []) as $value => $option) {
            $options[(string) $value] = is_array($option)
                ? ['label' => (string) ($option['label'] ?? $option[0] ?? $value), 'description' => $option['description'] ?? $option[1] ?? null]
                : ['label' => (string) $option, 'description' => null];
        }

        $subFields = [];
        foreach ((array) ($definition['fields'] ?? []) as $name => $sub) {
            $subFields[(string) $name] = is_array($sub)
                ? ['label' => (string) ($sub['label'] ?? Str::headline($name)), 'type' => (string) ($sub['type'] ?? 'text')]
                : ['label' => (string) $sub, 'type' => 'text'];
        }

        $itemRules = $definition['item_rules'] ?? [];
        if ($type === 'repeater') {
            $itemRules = collect((array) $itemRules)->map(fn ($r) => $this->ruleList($r))->all();
        } else {
            $itemRules = $this->ruleList($itemRules);
        }

        return [
            'key' => $key,
            'group' => (string) ($definition['group'] ?? Str::before($key, '.')),
            'type' => $type,
            'label' => (string) ($definition['label'] ?? Str::headline(Str::afterLast($key, '.'))),
            'help' => $definition['help'] ?? null,
            'section' => isset($definition['section']) ? (string) $definition['section'] : null,
            'default' => $definition['default'] ?? null,
            'placeholder' => $definition['placeholder'] ?? null,
            'unit' => $definition['unit'] ?? null,
            'options' => $options,
            'fields' => $subFields,
            'rules' => $rules,
            'item_rules' => $itemRules,
            'required' => in_array('required', $rules, true),
            'min' => $this->ruleParameter($rules, 'min'),
            'max' => $this->ruleParameter($rules, 'max'),
            'name' => self::inputName($key),
            'path' => self::INPUT.'.'.$key,
            'id' => self::inputId($key),
            'previewable' => $type === 'choice' && AppearancePreview::supports($key),
        ];
    }

    /**
     * Rules as an array ("a|b" strings are split; arrays are kept as they are).
     *
     * @return array<int, mixed>
     */
    private function ruleList(mixed $rules): array
    {
        if (is_string($rules)) {
            return $rules === '' ? [] : explode('|', $rules);
        }

        return array_values((array) $rules);
    }

    /**
     * Prepend a rule unless the list already contains it.
     *
     * @param  array<int, mixed>  $rules
     * @return array<int, mixed>
     */
    private function withRule(array $rules, string $rule): array
    {
        return in_array($rule, $rules, true) ? $rules : array_merge([$rule], $rules);
    }

    /**
     * Numeric parameter of a string rule such as "max:120" (for HTML attributes).
     *
     * @param  array<int, mixed>  $rules
     */
    private function ruleParameter(array $rules, string $name): ?string
    {
        foreach ($rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, $name.':')) {
                $value = Str::after($rule, ':');

                return is_numeric($value) ? $value : null;
            }
        }

        return null;
    }
}
