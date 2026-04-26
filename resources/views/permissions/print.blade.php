<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الصلاحيات</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', sans-serif; box-sizing: border-box; }
        
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11px; background: white !important; }
            .page-break { page-break-before: always; }
            table { page-break-inside: auto; width: 100%; border-collapse: collapse; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            th, td { border: 1px solid #ddd !important; padding: 6px !important; }
            .section-title { page-break-after: avoid; }
        }

        body {
            background: #f3f4f6;
            padding: 20px;
            font-size: 14px;
            line-height: 1.6;
        }

        .print-container {
            background: white;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .print-header {
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .section-title {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 12px 16px;
            border-right: 4px solid #3b82f6;
            margin: 25px 0 15px;
            font-weight: 800;
            font-size: 16px;
            color: #1e293b;
            border-radius: 0 8px 8px 0;
        }

        .permission-badge {
            display: inline-block;
            padding: 3px 8px;
            margin: 2px;
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 4px;
            font-size: 10px;
            border: 1px solid #bae6fd;
        }

        .role-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            color: white;
            font-size: 11px;
            font-weight: 700;
        }

        .stat-box {
            text-align: center;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
        }

        .stat-box h4 {
            font-size: 24px;
            font-weight: 900;
            color: #1e293b;
            margin: 0;
        }

        .stat-box small {
            color: #64748b;
            font-size: 12px;
        }

        table {
            font-size: 11px;
            border-collapse: collapse;
            width: 100%;
        }

        table th {
            background: #f1f5f9;
            font-weight: 700;
            text-align: right;
            padding: 8px;
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
            font-size: 10px;
            text-transform: uppercase;
        }

        table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        table tbody tr:hover {
            background: #f8fafc;
        }

        .user-name {
            font-weight: 700;
            color: #1e293b;
        }

        @page {
            margin: 15mm;
            size: A4;
        }

        @media print {
            .print-container {
                box-shadow: none;
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

    <!-- شريط الأدوات (يختفي عند الطباعة) -->
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" 
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold mx-2">
            <i class="fas fa-print ml-2"></i>طباعة التقرير
        </button>
        <button onclick="window.close()" 
                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold mx-2">
            <i class="fas fa-times ml-2"></i>إغلاق
        </button>
    </div>

    <div class="print-container">
        <!-- رأس التقرير -->
        <div class="print-header flex items-center justify-between">
            <div class="flex items-center gap-4">
                @if(session('company_logo'))
                <img src="{{ session('company_logo') }}" alt="شعار الشركة" style="height: 60px; max-width: 150px;">
                @endif
                <div>
                    <h2 class="text-xl font-bold mb-1">{{ session('company_name', 'اسم الشركة') }}</h2>
                    @if(session('company_address'))
                    <p class="text-gray-600 text-sm mb-0">{{ session('company_address') }}</p>
                    @endif
                    @if(session('company_tax_number'))
                    <p class="text-gray-500 text-xs mb-0">الرقم الضريبي: {{ session('company_tax_number') }}</p>
                    @endif
                </div>
            </div>
            <div class="text-left">
                <h3 class="text-lg font-bold text-gray-700 mb-1">تقرير الصلاحيات والأدوار</h3>
                <p class="text-gray-500 text-xs">التاريخ: {{ \Carbon\Carbon::now()->format('Y-m-d') }}</p>
            </div>
        </div>

        <!-- ملخص إحصائيات -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="stat-box">
                <h4>{{ $users->count() }}</h4>
                <small>مستخدم</small>
            </div>
            <div class="stat-box">
                <h4>{{ $roles->count() }}</h4>
                <small>دور</small>
            </div>
            <div class="stat-box">
                <h4>{{ $allPermissions->count() }}</h4>
                <small>صلاحية</small>
            </div>
            <div class="stat-box">
                <h4>{{ \App\Models\Permission::select('module')->distinct()->count() }}</h4>
                <small>موديول</small>
            </div>
        </div>

        <!-- الأدوار وصلاحياتها -->
        <div class="section-title">
            <i class="fas fa-user-tag ml-2 text-blue-600"></i>
            الأدوار وصلاحياتها
        </div>

        @foreach($roles as $role)
        <div class="mb-6 pb-4 border-b border-gray-200 last:border-b-0">
            <h5 class="font-bold text-gray-800 mb-2">
                <span class="role-badge px-3 py-1 text-sm" style="background-color: {{ $role->color }}">
                    {{ $role->display_name }}
                </span>
                <small class="text-gray-500 font-normal">@{{ $role->name }}</small>
                <span class="badge bg-gray-200 text-gray-700 text-xs px-2 py-1">
                    {{ $role->users->count() }} مستخدم
                </span>
            </h5>
            @if($role->description)
            <p class="text-gray-600 text-sm mb-3">{{ $role->description }}</p>
            @endif

            <div class="space-y-2">
                @foreach($role->permissions->groupBy('module') as $module => $permissions)
                <div>
                    <strong class="text-sm text-gray-700">
                        @php
                            $moduleName = trans()->has('modules.' . $module) ? __('modules.' . $module) : $module;
                        @endphp
                        {{ $moduleName }}:
                    </strong>
                    <div class="mt-1">
                        @foreach($permissions as $permission)
                            <span class="permission-badge">
                                {{ $permission->action }} - {{ $permission->display_name }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="page-break"></div>

        <!-- المستخدمين وصلاحياتهم -->
        <div class="section-title">
            <i class="fas fa-users ml-2 text-green-600"></i>
            المستخدمين وصلاحياتهم
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">المستخدم</th>
                    <th style="width: 25%;">البريد الإلكتروني</th>
                    <th style="width: 25%;">الأدوار</th>
                    <th style="width: 30%;">الصلاحيات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <span class="user-name">{{ $user->name }}</span>
                        @if($user->is_active)
                            <span class="badge bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded">نشط</span>
                        @else
                            <span class="badge bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded">غير نشط</span>
                        @endif
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->roles->count() > 0)
                            @foreach($user->roles as $role)
                                <span class="role-badge me-1 mb-1" style="background-color: {{ $role->color }}">
                                    {{ $role->display_name }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-gray-400 text-sm">لا يوجد</span>
                        @endif
                    </td>
                    <td>
                        <small>
                            <strong>{{ count($user->allPermissions()) }} صلاحية</strong>
                            @if(count($user->allPermissions()) > 0)
                                <div class="mt-1">
                                    @foreach(array_slice($user->allPermissions(), 0, 8) as $perm)
                                        <span class="permission-badge">{{ $perm }}</span>
                                    @endforeach
                                    @if(count($user->allPermissions()) > 8)
                                        <div class="text-gray-400 text-xs">
                                            +{{ count($user->allPermissions()) - 8 }} أخرى
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </small>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- جميع الصلاحيات -->
        <div class="page-break"></div>

        <div class="section-title">
            <i class="fas fa-key ml-2 text-amber-600"></i>
            جميع الصلاحيات في النظام
        </div>

        @foreach($allPermissions->groupBy('module') as $module => $permissions)
        <div class="mb-4">
            <h5 class="font-bold text-gray-800 mb-3">
                <i class="fas fa-folder text-amber-500 ml-2"></i>
                @php
                    $moduleName = trans()->has('modules.' . $module) ? __('modules.' . $module) : $module;
                @endphp
                {{ $moduleName }}
                <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded mr-2">
                    {{ $permissions->count() }} صلاحية
                </span>
            </h5>
            <table>
                <thead>
                    <tr>
                        <th style="width: 20%;">الإجراء</th>
                        <th style="width: 40%;">اسم الصلاحية</th>
                        <th style="width: 40%;">الوصف</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($permissions as $permission)
                    <tr>
                        <td><code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $permission->action }}</code></td>
                        <td>{{ $permission->display_name }}</td>
                        <td class="text-gray-600">{{ $permission->description ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach

        <!-- تذييل التقرير -->
        <div class="mt-6 pt-4 border-top text-center text-gray-500 text-sm">
            <p class="mb-1">تم إنشاء هذا التقرير بواسطة نظام إدارة المخازن - Magzani</p>
            <p class="mb-0">تاريخ الطباعة: {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>

</body>
</html>
