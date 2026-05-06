<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Transaction.php';

$auth = new Auth();
$auth->requireLogin();

$page_title = 'Transactions';
include 'includes/header.php';

$transaction = new Transaction();

// Handle filters
$filter_type = $_GET['type'] ?? '';
$filter_category = $_GET['category'] ?? '';
$filter_start_date = $_GET['start_date'] ?? '';
$filter_end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter criteria
$filter_data = [
    'type' => $filter_type,
    'category_id' => $filter_category,
    'start_date' => $filter_start_date,
    'end_date' => $filter_end_date,
    'search' => $search
];

// Get total count for pagination
$total_records = $transaction->countFilteredTransactions($filter_data);
$total_pages = ceil($total_records / $limit);

// Get paginated transactions
$transactions = $transaction->getFilteredTransactions($filter_data, $limit, $offset);

// Get categories for filter dropdown
$categories = $transaction->getCategories();
?>

<div class="page-animate w-full py-8 px-4">
    <!-- Pro Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="animate-fade-in-up">
            <div class="flex items-center gap-4 mb-2">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">Transactions</h1>
                <div class="bg-brand text-white px-4 py-1.5 rounded-2xl text-lg font-black shadow-lg shadow-brand/20 amount">
                    <?php echo $total_records; ?>
                </div>
            </div>
            <p class="text-gray-500 font-medium text-lg">Manage and view all your income and expenses.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 no-print animate-fade-in-up" style="animation-delay: 100ms;">
            <a href="export_transactions_csv.php?<?php echo http_build_query($_GET); ?>" class="px-5 py-3 bg-emerald-50 text-emerald-700 font-bold rounded-2xl hover:bg-emerald-600 hover:text-white transition-all duration-300 flex items-center shadow-sm border border-emerald-100 group">
                <i class="fas fa-file-csv mr-2 group-hover:rotate-12 transition-transform"></i>Export CSV
            </a>
            <a href="export_pdf.php?<?php echo http_build_query($_GET); ?>" class="px-5 py-3 bg-rose-50 text-rose-700 font-bold rounded-2xl hover:bg-rose-600 hover:text-white transition-all duration-300 flex items-center shadow-sm border border-rose-100 group">
                <i class="fas fa-file-pdf mr-2 group-hover:scale-110 transition-transform"></i>Export PDF
            </a>
            <a href="add_transaction.php" class="px-6 py-3 bg-brand text-white font-bold rounded-2xl hover:bg-brand-dark transition-all duration-300 flex items-center shadow-xl shadow-brand/20 transform active:scale-95">
                <i class="fas fa-plus-circle mr-2"></i>Add Transaction
            </a>
        </div>
    </div>

    <!-- Premium Filter Console (Accordion on Mobile) -->
    <div class="card-premium mb-8 border-[3px] border-gray-200 shadow-xl overflow-hidden group" id="filterConsole">
        <!-- Console Header (The Toggle) -->
        <div class="flex items-center justify-between p-1 cursor-pointer md:cursor-default" id="filterToggle">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-brand/10 text-brand flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform duration-500">
                    <i class="fas fa-sliders-h text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-800 tracking-tight">Filter Console</h2>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em]">Refine & Analyze Data</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="event.stopPropagation(); window.location.href='transactions.php'" 
                        class="hidden md:flex px-4 py-2 text-[10px] font-black text-gray-400 hover:text-brand hover:bg-brand/5 rounded-xl uppercase tracking-widest transition-all">
                    <i class="fas fa-sync-alt mr-2"></i> Reset
                </button>
                <!-- Mobile Indicator -->
                <div class="md:hidden w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 transition-transform duration-500" id="filterIcon">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>

        <!-- Filter Content -->
        <div class="hidden md:block mt-8 pt-8 border-t border-gray-100" id="filterContent">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6" id="filterForm">
                <!-- Search Input -->
                <div class="relative group">
                    <label for="search" class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1">Search Keywords</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-brand transition-colors"></i>
                        <input type="text" id="search" name="search" placeholder="Enter keywords..." 
                               class="w-full pl-11 pr-4 py-3.5 bg-white border-[3px] border-gray-100 rounded-2xl focus:border-brand focus:ring-4 focus:ring-brand/5 outline-none transition-all font-bold text-sm text-gray-700 shadow-sm"
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>

                <!-- Type Filter -->
                <div>
                    <label for="type" class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1">Flow Type</label>
                    <div class="relative">
                        <i class="fas fa-exchange-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
                        <select id="type" name="type" onchange="this.form.submit()"
                                class="w-full pl-11 pr-4 py-3.5 bg-white border-[3px] border-gray-100 rounded-2xl focus:border-brand focus:ring-4 focus:ring-brand/5 outline-none appearance-none transition-all font-bold text-sm text-gray-700 shadow-sm cursor-pointer">
                            <option value="">All Streams</option>
                            <option value="income" <?php echo $filter_type == 'income' ? 'selected' : ''; ?>>Income</option>
                            <option value="expense" <?php echo $filter_type == 'expense' ? 'selected' : ''; ?>>Expense</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                    </div>
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category" class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1">Classification</label>
                    <div class="relative">
                        <i class="fas fa-tags absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
                        <select id="category" name="category" onchange="this.form.submit()"
                                class="w-full pl-11 pr-4 py-3.5 bg-white border-[3px] border-gray-100 rounded-2xl focus:border-brand focus:ring-4 focus:ring-brand/5 outline-none appearance-none transition-all font-bold text-sm text-gray-700 shadow-sm cursor-pointer">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $filter_category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                    </div>
                </div>

                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1">Origin Date</label>
                    <div class="relative">
                        <i class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
                        <input type="text" id="start_date" name="start_date" placeholder="Select origin" 
                               class="w-full pl-11 pr-4 py-3.5 bg-white border-[3px] border-gray-100 rounded-2xl focus:border-brand focus:ring-4 focus:ring-brand/5 outline-none transition-all font-bold text-sm text-gray-700 shadow-sm"
                               value="<?php echo htmlspecialchars($filter_start_date); ?>">
                    </div>
                </div>

                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1">Target Date</label>
                    <div class="relative">
                        <i class="fas fa-calendar-check absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
                        <input type="text" id="end_date" name="end_date" placeholder="Select target" 
                               class="w-full pl-11 pr-4 py-3.5 bg-white border-[3px] border-gray-100 rounded-2xl focus:border-brand focus:ring-4 focus:ring-brand/5 outline-none transition-all font-bold text-sm text-gray-700 shadow-sm"
                               value="<?php echo htmlspecialchars($filter_end_date); ?>">
                    </div>
                </div>

                <div class="lg:col-span-5 flex justify-end gap-3 pt-4 border-t border-gray-100 mt-2">
                    <button type="button" onclick="window.location.href='transactions.php'" class="md:hidden px-6 py-4 bg-gray-50 text-gray-500 font-bold rounded-2xl flex-1">
                        Clear All
                    </button>
                    <button type="submit" class="px-10 py-4 bg-brand text-white font-black rounded-2xl hover:bg-brand-dark shadow-xl shadow-brand/20 transition-all flex-1 md:flex-none">
                        Apply Selection
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table Card -->
    <div class="card-premium overflow-hidden">
        <div class="bg-white p-6 border-b border-gray-100 flex justify-between items-center">
            <h5 class="text-sm font-black text-gray-700 m-0 uppercase tracking-widest">Global Transaction Ledger</h5>
            <div class="flex items-center gap-3">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Showing <?php echo count($transactions); ?> of <?php echo $total_records; ?></span>
            </div>
        </div>
        
        <div class="p-0">
            <?php if (empty($transactions)): ?>
                <div class="text-center py-24 text-gray-400 bg-gray-50/30">
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6 border border-gray-100 shadow-sm text-gray-200">
                        <i class="fas fa-search-dollar text-4xl animate-pulse"></i>
                    </div>
                    <h4 class="text-xl font-black text-gray-800 mb-2 tracking-tight">No Financial Records Found</h4>
                    <p class="text-sm font-medium">Try broadening your search or selection criteria.</p>
                </div>
            <?php else: ?>
                <!-- Desktop View (Hidden on Mobile) -->
                <div class="hidden md:block w-full hide-dt-search">
                    <table id="transactionTable" class="display stripe hover w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-200 text-[10px] uppercase tracking-[0.2em] text-gray-400 font-black">
                                <th class="px-6 py-5 text-left">Chronology</th>
                                <th class="px-6 py-5 text-left">Detailed Descriptor</th>
                                <th class="px-6 py-5 text-left">Status/Type</th>
                                <th class="px-6 py-5 text-right">Value Index</th>
                                <th class="px-6 py-5 text-right no-print">Governance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php 
                            $overall_income = $transaction->getTotalIncome($filter_start_date, $filter_end_date);
                            $overall_expenses = $transaction->getTotalExpenses($filter_start_date, $filter_end_date);
                            
                            foreach ($transactions as $t): 
                            ?>
                                <tr class="hover:bg-gray-50/80 transition-all duration-300 group">
                                    <td class="px-6 py-5 text-left border-l-4 border-transparent group-hover:border-brand">
                                        <div class="text-sm font-black text-gray-800 tracking-tight"><?php echo date('M d, Y', strtotime($t['transaction_date'])); ?></div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest opacity-60"><?php echo date('h:i A', strtotime($t['transaction_date'])); ?></div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 rounded-2xl <?php echo $t['type'] == 'income' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'; ?> flex items-center justify-center font-bold mr-5 shadow-sm border border-gray-100/50 group-hover:scale-110 transition-transform">
                                                <i class="fas <?php echo $t['type'] == 'income' ? 'fa-arrow-up' : 'fa-arrow-down'; ?> text-xs"></i>
                                            </div>
                                            <div class="max-w-[320px]">
                                                <div class="description-container text-justify leading-relaxed">
                                                    <?php 
                                                    $desc = $t['description'];
                                                    if (strlen($desc) > 30): 
                                                        $short_desc = mb_strimwidth($desc, 0, 30, "...");
                                                    ?>
                                                        <strong class="text-gray-900 text-sm font-black short-desc"><?php echo htmlspecialchars($short_desc); ?></strong>
                                                        <strong class="text-gray-900 text-sm font-black full-desc hidden"><?php echo htmlspecialchars($desc); ?></strong>
                                                        <button class="toggle-description text-brand hover:text-brand-dark text-[9px] font-black uppercase ml-1 focus:outline-none tracking-widest border-b border-brand/30">Read More</button>
                                                    <?php else: ?>
                                                        <strong class="text-gray-900 block text-sm font-black"><?php echo htmlspecialchars($desc); ?></strong>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex items-center gap-2 mt-2">
                                                    <span class="text-[9px] font-black text-gray-500 uppercase bg-gray-50 px-2 py-1 rounded-lg border border-gray-100 shadow-xs"><i class="fas fa-fingerprint mr-1 text-[8px] opacity-40"></i><?php echo htmlspecialchars($t['category_name'] ?? 'General'); ?></span>
                                                    <?php if($t['notes']): ?>
                                                        <span class="text-[10px] text-gray-400 italic truncate max-w-[180px] border-l-2 border-gray-100 pl-3 ml-1 opacity-70"><?php echo htmlspecialchars($t['notes']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <?php if ($t['type'] == 'income'): ?>
                                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Revenue
                                            </div>
                                        <?php else: ?>
                                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest bg-rose-50 text-rose-700 border border-rose-100 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                Expenditure
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="text-lg font-black amount <?php echo $t['type'] == 'income' ? 'text-emerald-600' : 'text-rose-600'; ?> tracking-tighter">
                                            <?php echo $t['type'] == 'income' ? '+' : '-'; ?><?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($t['amount'], 2); ?>
                                        </div>
                                        <div class="text-[9px] text-gray-400 font-bold uppercase tracking-[0.2em] opacity-50">Verified Value</div>
                                    </td>
                                    <td class="px-6 py-5 text-right no-print">
                                        <div class="flex items-center justify-end gap-3">
                                            <?php if (!empty($t['attachment_path'])): 
                                                $file_ext = strtolower(pathinfo($t['attachment_path'], PATHINFO_EXTENSION));
                                                $icon_color = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif']) ? 'text-blue-500' : ($file_ext == 'pdf' ? 'text-rose-500' : 'text-gray-400');
                                            ?>
                                                <a href="<?php echo htmlspecialchars($t['attachment_path']); ?>" target="_blank" 
                                                   class="w-10 h-10 rounded-2xl bg-gray-50 hover:bg-white transition-all duration-300 border border-gray-100 hover:border-brand shadow-sm flex items-center justify-center group/btn" 
                                                   title="View Evidence">
                                                    <i class="fas <?php echo ($file_ext == 'pdf' ? 'fa-file-pdf' : 'fa-image'); ?> <?php echo $icon_color; ?> group-hover/btn:scale-110 transition-transform"></i>
                                                </a>
                                            <?php else: ?>
                                                <div class="w-10 h-10 rounded-2xl bg-gray-50/30 border border-dashed border-gray-200 flex items-center justify-center opacity-30">
                                                    <i class="fas fa-eye-slash text-gray-400 text-xs"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="h-8 w-px bg-gray-100 mx-1"></div>

                                            <a href="edit_transaction.php?id=<?php echo $t['id']; ?>" class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 hover:bg-indigo-600 hover:text-white transition-all shadow-sm group/edit">
                                                <i class="fas fa-edit text-xs group-hover/edit:rotate-12"></i>
                                            </a>
                                            <button onclick="if(confirm('Are you sure?')) window.location.href='delete_transaction.php?id=<?php echo $t['id']; ?>';" 
                                                    class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100 hover:bg-rose-600 hover:text-white transition-all shadow-sm group/del">
                                                <i class="fas fa-trash-alt text-xs group-hover/del:shake"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-gray-50/50">
                            <tr>
                                <th colspan="3" class="px-8 py-10 text-right">
                                    <div class="flex flex-col items-end gap-1.5">
                                        <span class="text-[11px] font-black text-gray-400 uppercase tracking-[0.3em]">Institutional Account Balance</span>
                                        <div class="h-1 w-24 bg-brand/20 rounded-full mb-1"></div>
                                        <span class="text-sm font-black text-gray-600 uppercase tracking-widest">Global Financial Position</span>
                                    </div>
                                </th>
                                <th class="px-8 py-10 text-right border-t-2 border-gray-200 bg-gray-50/80">
                                    <div class="font-mono space-y-3 balance">
                                        <div class="flex justify-end items-center gap-5">
                                            <span class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Total Inflow</span>
                                            <span class="text-base font-black text-emerald-600 bg-white px-4 py-1.5 rounded-2xl border border-emerald-100 shadow-sm amount">
                                                +<?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($overall_income, 2); ?>
                                            </span>
                                        </div>
                                        <div class="flex justify-end items-center gap-5">
                                            <span class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Total Outflow</span>
                                            <span class="text-base font-black text-rose-600 bg-white px-4 py-1.5 rounded-2xl border border-rose-100 shadow-sm amount">
                                                -<?php echo CURRENCY_SYMBOL; ?> <?php echo number_format($overall_expenses, 2); ?>
                                            </span>
                                        </div>
                                        <div class="pt-4 mt-4 border-t-2 border-dashed border-gray-200">
                                            <div class="flex justify-end items-center gap-6">
                                                <span class="text-[11px] text-gray-600 font-black uppercase tracking-[0.2em]">Net Treasury</span>
                                                <span class="text-2xl font-black <?php echo ($overall_income - $overall_expenses) >= 0 ? 'text-emerald-700' : 'text-rose-700'; ?> decoration-brand/30 decoration-4 underline underline-offset-8 amount">
                                                    <?php echo ($overall_income - $overall_expenses) >= 0 ? '' : '-'; ?><?php echo CURRENCY_SYMBOL; ?> <?php echo number_format(abs($overall_income - $overall_expenses), 2); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="no-print"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Mobile View (Hidden on Desktop) -->
                <div class="md:hidden divide-y divide-gray-100">
                    <?php foreach ($transactions as $t): ?>
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl <?php echo $t['type'] == 'income' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'; ?> flex items-center justify-center shadow-sm border border-gray-100">
                                        <i class="fas <?php echo $t['type'] == 'income' ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5"><?php echo date('M d, Y', strtotime($t['transaction_date'])); ?></div>
                                        <div class="text-base font-black text-gray-900 line-clamp-1 tracking-tight"><?php echo htmlspecialchars($t['description']); ?></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-black amount <?php echo $t['type'] == 'income' ? 'text-emerald-600' : 'text-rose-600'; ?> tracking-tighter">
                                        <?php echo $t['type'] == 'income' ? '+' : '-'; ?><?php echo CURRENCY_SYMBOL . number_format($t['amount'], 2); ?>
                                    </div>
                                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest"><?php echo htmlspecialchars($t['category_name'] ?? 'General'); ?></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex gap-2">
                                    <?php if (!empty($t['attachment_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($t['attachment_path']); ?>" target="_blank" class="px-3 py-2 bg-white rounded-xl text-[10px] font-black text-gray-600 border border-gray-100 shadow-sm flex items-center gap-2">
                                            <i class="fas fa-paperclip text-brand"></i> EVIDENCE
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="flex gap-3">
                                    <a href="edit_transaction.php?id=<?php echo $t['id']; ?>" class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <button type="button" onclick="if(confirm('Are you sure?')) window.location.href='delete_transaction.php?id=<?php echo $t['id']; ?>';" class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Mobile Summary -->
                    <div class="p-8 bg-gray-50/80 mt-4 rounded-b-3xl border-t border-gray-200">
                        <div class="text-[11px] font-black text-gray-400 uppercase tracking-[0.3em] mb-6 text-center">Executive Financial Summary</div>
                        <div class="grid grid-cols-2 gap-4 mb-5">
                            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
                                <div class="text-[9px] font-black text-gray-400 uppercase mb-2">Total Inflow</div>
                                <div class="text-base font-black text-emerald-600 amount">+<?php echo CURRENCY_SYMBOL . number_format($overall_income, 2); ?></div>
                            </div>
                            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
                                <div class="text-[9px] font-black text-gray-400 uppercase mb-2">Total Outflow</div>
                                <div class="text-base font-black text-rose-600 amount">-<?php echo CURRENCY_SYMBOL . number_format($overall_expenses, 2); ?></div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border-2 border-brand/20 shadow-xl text-center ring-4 ring-brand/5">
                            <div class="text-[11px] font-black text-gray-500 uppercase mb-2 tracking-widest">Net Treasury Balance</div>
                            <div class="text-2xl font-black amount <?php echo ($overall_income - $overall_expenses) >= 0 ? 'text-emerald-700' : 'text-rose-700'; ?>">
                                <?php echo ($overall_income - $overall_expenses) >= 0 ? '' : '-'; ?><?php echo CURRENCY_SYMBOL . number_format(abs($overall_income - $overall_expenses), 2); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Pro Pagination Section -->
            <?php if ($total_pages > 1): ?>
                <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/20 flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-[11px] font-black text-gray-400 uppercase tracking-widest order-2 md:order-1">
                        Page <span class="text-brand"><?php echo $page; ?></span> of <?php echo $total_pages; ?> • Total <span class="text-gray-600"><?php echo $total_records; ?></span> Records
                    </div>
                    <div class="flex items-center gap-2 order-1 md:order-2">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-brand hover:border-brand transition-all shadow-sm" title="First Page">
                                <i class="fas fa-angle-double-left text-xs"></i>
                            </a>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-[11px] font-black text-gray-500 uppercase tracking-widest hover:text-brand hover:border-brand transition-all shadow-sm">
                                Previous
                            </a>
                        <?php endif; ?>

                        <div class="flex items-center gap-1.5 px-3">
                            <?php 
                            $start_p = max(1, $page - 1);
                            $end_p = min($total_pages, $page + 1);
                            for ($i = $start_p; $i <= $end_p; $i++): 
                            ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="w-10 h-10 rounded-xl flex items-center justify-center text-[11px] font-black transition-all shadow-sm <?php echo $i == $page ? 'bg-brand text-white shadow-brand/30' : 'bg-white border border-gray-200 text-gray-500 hover:text-brand hover:border-brand'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-[11px] font-black text-gray-500 uppercase tracking-widest hover:text-brand hover:border-brand transition-all shadow-sm">
                                Next
                            </a>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-brand hover:border-brand transition-all shadow-sm" title="Last Page">
                                <i class="fas fa-angle-double-right text-xs"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Flatpickr
        flatpickr("#start_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
        });
        flatpickr("#end_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
        });

        // Mobile Filter Accordion Logic
        const filterToggle = document.getElementById('filterToggle');
        const filterContent = document.getElementById('filterContent');
        const filterIcon = document.getElementById('filterIcon');

        if (filterToggle && filterContent && window.innerWidth < 768) {
            filterToggle.addEventListener('click', function() {
                const isHidden = filterContent.classList.contains('hidden');
                
                if (isHidden) {
                    filterContent.classList.remove('hidden');
                    filterContent.classList.add('block');
                    filterIcon.style.transform = 'rotate(180deg)';
                } else {
                    filterContent.classList.remove('block');
                    filterContent.classList.add('hidden');
                    filterIcon.style.transform = 'rotate(0deg)';
                }
            });
        }
    });
</script>

<?php if (isset($_SESSION['success_message'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof showCornerNotification === 'function') {
            showCornerNotification("<?php echo addslashes($_SESSION['success_message']); ?>", 'success');
        }
    });
</script>
<?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>