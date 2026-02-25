/**
 * Rede Analytics React Component
 * Página de analytics das transações Rede com Grid.js
 */
import React, { useEffect, useRef, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { Grid, html } from 'gridjs';
import { decode } from '@toon-format/toon';
import 'gridjs/dist/theme/mermaid.css';

// Definição das colunas padrão
const DEFAULT_COLUMNS = [
    { id: 'gateway', name: 'Card/PIX', visible: true },
    { id: 'cvv_sent', name: 'CVV Enviado', visible: true },
    { id: 'type', name: 'Tipo', visible: true },
    { id: 'installments', name: 'Parcelas', visible: true },
    { id: 'installment_amount', name: 'Vlr. Parcela', visible: true },
    { id: 'brand', name: 'Bandeira', visible: true },
    { id: 'expiry', name: 'Vencimento', visible: true },
    { id: 'datetime', name: 'Data/Hora', visible: true },
    { id: 'total', name: 'Total', visible: true },
    { id: 'subtotal', name: 'Subtotal', visible: true },
    { id: 'shipping', name: 'Frete', visible: true },
    { id: 'interest_discount', name: 'Juros/Desc.', visible: true },
    { id: 'currency', name: 'Moeda', visible: true },
    { id: 'capture', name: 'Captura', visible: true },
    { id: 'recurrent', name: 'Recorrente', visible: true },
    { id: 'auth_3ds', name: '3DS Auth', visible: true },
    { id: 'tid', name: 'TID/PaymentId', visible: true },
    { id: 'environment', name: 'Ambiente', visible: true },
    { id: 'payment_gateway', name: 'Gateway', visible: true },
    { id: 'order_id', name: 'Order ID', visible: true },
    { id: 'reference', name: 'Reference', visible: true },
    { id: 'pv', name: 'PV', visible: true },
    { id: 'token', name: 'Token', visible: true },
    { id: 'return_code', name: 'Return Code', visible: true },
    { id: 'http_status', name: 'HTTP Status', visible: true },
    { id: 'holder_name', name: 'Portador', visible: true },
    { id: 'whatsapp', name: 'Suporte', visible: true }
];

// Componente principal para Analytics do Rede
// Sistema de Tooltip usando DOM puro para compatibilidade com Grid.js
class TooltipManager {
    private static instance: TooltipManager;
    private tooltipElement: HTMLDivElement | null = null;
    private arrowElement: HTMLDivElement | null = null;
    private currentTarget: HTMLElement | null = null;

    static getInstance(): TooltipManager {
        if (!TooltipManager.instance) {
            TooltipManager.instance = new TooltipManager();
        }
        return TooltipManager.instance;
    }

    private createTooltipElement(): HTMLDivElement {
        const tooltip = document.createElement('div');
        tooltip.className = 'rede-tooltip-manager';
        tooltip.style.cssText = `
            position: absolute;
            background-color: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 999999;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            pointer-events: none;
            display: none;
            min-width: fit-content;
            width: auto;
            height: auto;
        `;
        
        // Adicionar div para o conteúdo (sem position absolute)
        const content = document.createElement('div');
        content.className = 'tooltip-content';
        tooltip.appendChild(content);
        
        document.body.appendChild(tooltip);
        return tooltip;
    }

    private createArrowElement(): HTMLDivElement {
        const arrow = document.createElement('div');
        arrow.className = 'rede-tooltip-arrow';
        arrow.style.cssText = `
            position: absolute;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 6px solid #333;
            z-index: 999998;
            pointer-events: none;
            display: none;
        `;
        
        document.body.appendChild(arrow);
        return arrow;
    }

    showTooltip(target: HTMLElement, content: string): void {
        if (!this.tooltipElement) {
            this.tooltipElement = this.createTooltipElement();
        }
        if (!this.arrowElement) {
            this.arrowElement = this.createArrowElement();
        }

        this.currentTarget = target;
        
        // Definir conteúdo no div específico para o conteúdo
        const contentDiv = this.tooltipElement.querySelector('.tooltip-content') as HTMLElement;
        if (contentDiv) {
            contentDiv.textContent = content;
        } else {
            // Fallback caso o elemento não seja encontrado
            this.tooltipElement.textContent = content;
        }
        
        // Aplicar estilo simples sem corte de texto
        this.tooltipElement.style.whiteSpace = 'normal';
        this.tooltipElement.style.maxWidth = '300px';
        this.tooltipElement.style.wordBreak = 'break-word';
        this.tooltipElement.style.display = 'block';
        this.tooltipElement.style.overflow = 'visible';
        this.tooltipElement.style.lineHeight = '1.4';
        
        // Calcular posição base
        const rect = target.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        
        // Posição da setinha: mais acima do elemento
        const arrowTop = rect.top + scrollTop - 15; // 15px acima do elemento
        const arrowLeft = rect.left + scrollLeft + (rect.width / 2);
        
        // Posicionar a setinha
        this.arrowElement.style.top = arrowTop + 'px';
        this.arrowElement.style.left = arrowLeft + 'px';
        this.arrowElement.style.transform = 'translateX(-50%)';
        this.arrowElement.style.display = 'block';
        
        const tooltipTop = Math.ceil(arrowTop);
        const tooltipLeft = arrowLeft;
        
        // Posicionar o balão grudado na setinha
        this.tooltipElement.style.top = tooltipTop + 'px';
        this.tooltipElement.style.left = tooltipLeft + 'px';
        this.tooltipElement.style.transform = 'translateX(-50%) translateY(-100%)';
    }

    hideTooltip(): void {
        if (this.tooltipElement) {
            this.tooltipElement.style.display = 'none';
        }
        if (this.arrowElement) {
            this.arrowElement.style.display = 'none';
        }
        this.currentTarget = null;
    }

    private constructor() {
        // Esconder tooltip quando o mouse sair da área
        document.addEventListener('mouseover', (e) => {
            if (this.currentTarget && !this.currentTarget.contains(e.target as Node) && e.target !== this.tooltipElement) {
                this.hideTooltip();
            }
        });
        
        // Esconder tooltip quando Shift for pressionado (para evitar conflito com Shift+scroll)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Shift' && this.currentTarget) {
                this.hideTooltip();
            }
        });
    }
}

const RedeAnalyticsPage = () => {
    const gridRef = useRef<HTMLDivElement>(null);
    const [transactionData, setTransactionData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [loadingMore, setLoadingMore] = useState(false);
    const [error, setError] = useState(null);
    
    // Estados para configuração de colunas
    const [columnConfig, setColumnConfig] = useState(DEFAULT_COLUMNS);
    const [showColumnConfig, setShowColumnConfig] = useState(false);
    const [draggedItem, setDraggedItem] = useState<number | null>(null);
    const [dragOverItem, setDragOverItem] = useState<number | null>(null);
    
    // Estados de paginação
    const [currentPage, setCurrentPage] = useState(1);
    const [hasNextPage, setHasNextPage] = useState(true);
    const [totalCount, setTotalCount] = useState(0);
    
    // Estados para filtros de data
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    const [activeFilter, setActiveFilter] = useState('hoje'); // 'hoje', '7dias', '30dias', 'personalizado'
    
    // Estados para configurações de paginação
    const [queryLimit, setQueryLimit] = useState(50); // Limite de consultas do banco (mais registros para encontrar transações Rede)
    const [perPageLimit, setPerPageLimit] = useState(10);     // Transações por página exibidas no grid

    // Função para decodificar dados TOON usando a biblioteca @toon-format/toon
    const decodeToonData = (toonString: string) => {
        try {
            return decode(toonString);
        } catch (e) {
            console.error('Erro ao decodificar TOON:', e);
            return null;
        }
    };

    // Funções para gerenciamento de colunas
    const saveColumnConfig = (config: typeof DEFAULT_COLUMNS) => {
        localStorage.setItem('rede_analytics_columns', JSON.stringify(config));
    };

    const loadColumnConfig = () => {
        try {
            const saved = localStorage.getItem('rede_analytics_columns');
            if (saved) {
                const parsed = JSON.parse(saved);
                
                // Filtrar apenas colunas válidas que existem em DEFAULT_COLUMNS
                const validColumns = parsed.filter((savedCol: any) => 
                    DEFAULT_COLUMNS.find(defaultCol => defaultCol.id === savedCol.id)
                );
                
                // Adicionar colunas padrão que não existem na configuração salva
                const mergedConfig = [...validColumns];
                DEFAULT_COLUMNS.forEach(defaultCol => {
                    if (!mergedConfig.find(col => col.id === defaultCol.id)) {
                        mergedConfig.push(defaultCol);
                    }
                });
                
                return mergedConfig;
            }
        } catch (e) {
            console.error('Erro ao carregar configuração de colunas:', e);
        }
        return DEFAULT_COLUMNS;
    };

    const moveColumn = (fromIndex: number, toIndex: number) => {
        const newConfig = [...columnConfig];
        const [movedItem] = newConfig.splice(fromIndex, 1);
        newConfig.splice(toIndex, 0, movedItem);
        setColumnConfig(newConfig);
        saveColumnConfig(newConfig);
    };

    const moveColumnUp = (index: number) => {
        if (index > 0) {
            moveColumn(index, index - 1);
        }
    };

    const moveColumnDown = (index: number) => {
        if (index < columnConfig.length - 1) {
            moveColumn(index, index + 1);
        }
    };

    const toggleColumnVisibility = (index: number) => {
        const newConfig = [...columnConfig];
        newConfig[index].visible = !newConfig[index].visible;
        setColumnConfig(newConfig);
        saveColumnConfig(newConfig);
    };

    const resetColumnConfig = () => {
        setColumnConfig(DEFAULT_COLUMNS);
        saveColumnConfig(DEFAULT_COLUMNS);
    };

    // Drag and Drop handlers
    const handleDragStart = (e: React.DragEvent, index: number) => {
        setDraggedItem(index);
        e.dataTransfer.effectAllowed = 'move';
    };

    const handleDragOver = (e: React.DragEvent, index: number) => {
        e.preventDefault();
        setDragOverItem(index);
    };

    const handleDragLeave = () => {
        setDragOverItem(null);
    };

    const handleDrop = (e: React.DragEvent, dropIndex: number) => {
        e.preventDefault();
        if (draggedItem !== null && draggedItem !== dropIndex) {
            moveColumn(draggedItem, dropIndex);
        }
        setDraggedItem(null);
        setDragOverItem(null);
    };

    // Função para decodificar entidades HTML corretamente
    const decodeHtmlEntities = (str: string) => {
        if (!str) return str;
        
        // Criar elemento temporário para decodificar entidades HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = str;
        return tempDiv.textContent || tempDiv.innerText || str;
    };

    // Função para mapear bandeira ao arquivo de imagem
    const getBrandImage = (brand: string) => {
        if (!brand || brand === 'N/A' || brand.trim() === '') {
            return null; // Não aplicar imagem
        }
        
        const brandLower = brand.toLowerCase();
        const gatewayBrandsUrl = (window as any).lknRedeAjax?.gateway_brands_url;
        
        if (!gatewayBrandsUrl) {
            return null;
        }
        
        // Mapear bandeiras conhecidas
        if (brandLower.includes('visa')) {
            return `${gatewayBrandsUrl}visa.webp`;
        } else if (brandLower.includes('master')) {
            return `${gatewayBrandsUrl}mastercard.webp`;
        } else if (brandLower.includes('elo')) {
            return `${gatewayBrandsUrl}elo.webp`;
        } else if (brandLower.includes('amex') || brandLower.includes('american express')) {
            return `${gatewayBrandsUrl}amex.webp`;
        } else if (brandLower.includes('diners')) {
            return `${gatewayBrandsUrl}diners.webp`;
        } else if (brandLower.includes('hipercard') || brandLower.includes('hiper')) {
            return `${gatewayBrandsUrl}hipercard.webp`;
        } else if (brandLower.includes('discover')) {
            return `${gatewayBrandsUrl}discover.webp`;
        } else if (brandLower.includes('jcb')) {
            return `${gatewayBrandsUrl}jcb.webp`;
        } else if (brandLower.includes('aura')) {
            return `${gatewayBrandsUrl}aura.webp`;
        } else if (brandLower.includes('paypal')) {
            return `${gatewayBrandsUrl}paypal.webp`;
        } else if (brandLower.includes('pix')) {
            return `${gatewayBrandsUrl}pix.webp`;
        } else {
            // Bandeira não reconhecida mas existe valor
            return `${gatewayBrandsUrl}other.webp`;
        }
    };

    // Função para gerar HTML de bandeira com tooltip
    const generateBrandTooltipHTML = (brand: string): string => {
        const imageUrl = getBrandImage(brand);
        
        if (!imageUrl) {
            return brand;
        }

        return `<img src="${imageUrl}" alt="${escapeHtml(brand)}" 
                     style="width: 24px; height: 16px; cursor: pointer; vertical-align: middle;" 
                     class="tooltip-trigger" 
                     data-tooltip="${escapeHtml(brand)}" />`;
    };

    // Função para gerar HTML de código com tooltip
    const generateCodeTooltipHTML = (code: string, label: string): string => {
        if (!code || code === 'N/A') {
            return code;
        }

        const shortCode = escapeHtml(code.split(' - ')[0]);
        const fullTooltip = escapeHtml(`${label}: ${code}`);

        return `<div style="display: inline-flex; align-items: center; gap: 5px;">
                    <span style="font-size: 12px;">${shortCode}</span>
                    <div class="tooltip-trigger" 
                         data-tooltip="${fullTooltip}" 
                         style="width: 16px; height: 16px; border-radius: 50%; background-color: #4A90E2; color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; cursor: pointer; user-select: none;">
                        i
                    </div>
                </div>`;
    };

    /**
     * Aplica a lógica de censura baseada no tamanho da string.
     * @param {string|number} value - O valor a ser mascarado.
     * @returns {string} - O valor mascarado.
     */
    function maskValue(value) {
        if (value === null || value === undefined || value === 'null') {
            return value;
        }

        const strValue = String(value);
        const len = strValue.length;

        // Mostra no máximo 4, mas nunca mais que 1/3 da string
        const keep = Math.min(4, Math.floor(len / 3)); 
        
        const start = strValue.slice(0, keep);
        
        // Tratamento do slice(-0)
        const safeEnd = keep > 0 ? strValue.slice(-keep) : '';
        
        // Preenchimento do meio
        const middle = '*'.repeat(Math.max(1, len - (keep * 2)));

        return `${start}${middle}${safeEnd}`;
    }

    // Função para gerar mensagem completa para debug
    const generateWhatsAppMessage = (transactionData: any) => {
        const pluginSlugs = {
            'integration_rede_pix': 'lkn-integration-rede-for-woocommerce',
            'rede_credit': 'lkn-integration-rede-for-woocommerce',
            'rede_debit': 'lkn-integration-rede-for-woocommerce',
            'rede_pix': 'rede-for-woocommerce-pro',
        }

        const analyticsData = (window as any).lknRedeAnalytics || {};

        const fields = [
            // Sistema
            `Pedido: ${transactionData.system?.order_id || 'N/A'}`,
            `Data/Hora: ${transactionData.system?.request_datetime || 'N/A'}`,
            `Ambiente: ${transactionData.system?.environment || 'N/A'}`,
            `Plugin: lkn-integration-rede-for-woocommerce v${transactionData.system?.version_free || 'N/A'} (Lançamento v${analyticsData.version_free || 'N/A'})`,
            `Plugin dependente: ${transactionData.system?.version_pro && transactionData.system?.version_pro !== 'N/A' ? `rede-for-woocommerce-pro v${transactionData.system?.version_pro || 'N/A'} (Lançamento v${analyticsData.version_pro || 'N/A'})` : 'N/A'}`,
            `Site: ${analyticsData.site_domain || 'N/A'}`,
            `Gateway: ${transactionData.system?.gateway || 'N/A'}`,
            `Reference: ${transactionData.system?.reference || 'N/A'}`,
            
            // Dados do cartão
            `Cartão/PIX: ${transactionData.gateway?.masked || 'N/A'}`,
            `CVV Enviado: ${transactionData.transaction?.cvv_sent || 'N/A'}`,
            `Tipo do Cartão: ${transactionData.gateway?.type || 'N/A'}`,
            `Bandeira: ${transactionData.gateway?.brand || 'N/A'}`,
            `Vencimento: ${transactionData.gateway?.expiry || 'N/A'}`,
            `Portador: ${transactionData.gateway?.holder_name || 'N/A'}`,
            
            // Dados da transação
            `Parcelas: ${transactionData.transaction?.installments || 'N/A'}`,
            `Valor Parcela: ${transactionData.transaction?.installment_amount || 'N/A'}`,
            `Captura: ${transactionData.transaction?.capture || 'N/A'}`,
            `Recorrente: ${transactionData.transaction?.recurrent || 'N/A'}`,
            `3DS Auth: ${transactionData.transaction?.['3ds_auth'] || 'N/A'}`,
            `TID/PaymentId: ${maskValue(transactionData.transaction?.tid) || 'N/A'}`,
            
            // Valores
            `Total: ${transactionData.amounts?.total || 'N/A'}`,
            `Subtotal: ${transactionData.amounts?.subtotal || 'N/A'}`,
            `Frete: ${transactionData.amounts?.shipping || 'N/A'}`,
            `Juros/Desc: ${transactionData.amounts?.interest_discount || 'N/A'}`,
            `Moeda: ${transactionData.amounts?.currency || 'N/A'}`,
            
            // Credentials
            `PV: ${transactionData.credentials?.pv_masked || 'N/A'}`,
            `Token: ${transactionData.credentials?.token_masked || 'N/A'}`,
            
            // Resposta da API (essencial para debug)
            `Return Code: ${transactionData.response?.return_code || 'N/A'}`,
            `HTTP Status: ${transactionData.response?.http_status || 'N/A'}`
        ];
        
        return `#suporte Olá! Preciso de suporte com meu gateway de pagamento Rede. Estou com problemas na transação e segue os dados para verificação: ${fields.join(' | ')}. Aguardo retorno, obrigado!`;
    };

    // Função para gerar link do WhatsApp
    const generateWhatsAppLink = (transactionData: any) => {
        const message = generateWhatsAppMessage(transactionData);
        return `https://api.whatsapp.com/send/?phone=${(window as any).lknRedeAjax.whatsapp_number || ''}&text=${encodeURIComponent(message)}`;
    };

    // Função para buscar dados via AJAX
    const fetchTransactionData = async (page = 1, append = false, customStartDate?: string, customEndDate?: string) => {
        try {
            if (page === 1) {
                setLoading(true);
            } else {
                setLoadingMore(true);
            }
            
            if (page === 1) {
                setError(null);
            }

            // Usar as datas customizadas se fornecidas, senão usar as do estado
            const effectiveStartDate = customStartDate !== undefined ? customStartDate : startDate;
            const effectiveEndDate = customEndDate !== undefined ? customEndDate : endDate;

            const response = await fetch((window as any).lknRedeAjax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: (window as any).lknRedeAjax.action_get_recent_orders,
                    nonce: (window as any).lknRedeAjax.nonce,
                    response_format: 'toon',
                    page: page.toString(),
                    query_limit: queryLimit.toString(),
                    start_date: effectiveStartDate,
                    end_date: effectiveEndDate
                })
            });

            // Verificar Content-Type para determinar formato da resposta
            const contentType = response.headers.get('Content-Type') || '';
            const isJsonResponse = contentType.includes('application/json');
            const isTextResponse = contentType.includes('text/plain');
            
            let result;
            
            if (isTextResponse) {
                // Resposta em formato TOON (text/plain)
                const responseText = await response.text();
                result = decodeToonData(responseText);
                
                if (!result) {
                    throw new Error('Falha ao decodificar resposta TOON');
                }
            } else if (isJsonResponse) {
                // Resposta em formato JSON padrão do WordPress
                result = await response.json();
                
                // Se é um wrapper JSON com dados TOON dentro
                if (result.success && result.data?.format === 'toon' && result.data?.toon_data) {
                    const toonData = decodeToonData(result.data.toon_data);
                    if (toonData) {
                        result = toonData;
                    }
                }
            } else {
                // Fallback: tentar como JSON primeiro, depois TOON
                try {
                    result = await response.json();
                } catch {
                    const responseText = await response.text();
                    result = decodeToonData(responseText);
                    
                    if (!result) {
                        throw new Error('Formato de resposta não reconhecido');
                    }
                }
            }

            if (result.success) {
                // Processar os dados recebidos
                let formattedData = [];
                if (result.data.orders && Array.isArray(result.data.orders)) {
                    formattedData = result.data.orders.map((order: any) => {
                        // Se tem transaction_data, usar diretamente
                        if (order.transaction_data) {
                            return {
                                ...order.transaction_data,
                                order_id: order.order_id,
                                data_format: order.data_format
                            };
                        }
                        // Se for formato antigo, usar order diretamente
                        return order;
                    }).filter(item => item !== null && item !== undefined);
                } else {
                    console.warn('Formato de dados inesperado:', result.data);
                    formattedData = [];
                }
                
                if (append) {
                    // Acumular dados existentes
                    setTransactionData(prev => [...prev, ...formattedData]);
                } else {
                    // Substituir dados
                    setTransactionData(formattedData);
                }
                
                // Atualizar estado de paginação
                const pagination = result.data.pagination;
                setCurrentPage(pagination.page);
                setHasNextPage(pagination.has_next);
                setTotalCount(pagination.total_count);
                
            } else {
                setError(result.data?.message || 'Erro ao carregar dados');
            }
        } catch (err) {
            const errorMessage = err instanceof Error ? err.message : 'Erro de conexão ao carregar dados';
            setError(errorMessage);
            console.error('Erro na requisição AJAX:', err);
        } finally {
            setLoading(false);
            setLoadingMore(false);
        }
    };

    // Função para carregar mais dados
    const loadMoreData = () => {
        if (hasNextPage && !loadingMore) {
            fetchTransactionData(currentPage + 1, true);
        }
    };

    // Função para aplicar filtros de data
    const applyDateFilters = () => {
        setCurrentPage(1);
        setTransactionData([]);
        fetchTransactionData(1, false);
    };

    // Função para limpar filtros de data  
    const clearDateFilters = () => {
        const today = new Date();
        const todayFormatted = formatDateForInput(today);
        
        setStartDate(todayFormatted);
        setEndDate(todayFormatted);
        setActiveFilter('hoje');
        setCurrentPage(1);
        setTransactionData([]);
        fetchTransactionData(1, false, todayFormatted, todayFormatted);
    };

    // Funções para filtros rápidos de data
    const formatDateForInput = (date: Date) => {
        return date.toISOString().split('T')[0];
    };

    const setDateFilter = (filterType: string) => {
        const today = new Date();
        let startDateValue = '';
        let endDateValue = formatDateForInput(today);

        switch (filterType) {
            case 'hoje':
                startDateValue = formatDateForInput(today);
                break;
            case '7dias':
                const sevenDaysAgo = new Date();
                sevenDaysAgo.setDate(today.getDate() - 7);
                startDateValue = formatDateForInput(sevenDaysAgo);
                break;
            case '30dias':
                const thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(today.getDate() - 30);
                startDateValue = formatDateForInput(thirtyDaysAgo);
                break;
            default:
                return;
        }

        // Atualizar os estados
        setStartDate(startDateValue);
        setEndDate(endDateValue);
        setActiveFilter(filterType);
        setCurrentPage(1);
        setTransactionData([]);
        
        // Aplicar filtro automaticamente passando as datas calculadas diretamente
        setTimeout(() => {
            fetchTransactionData(1, false, startDateValue, endDateValue);
        }, 0);
    };

    // Função para detectar quando as datas são alteradas manualmente
    const handleDateChange = (type: 'start' | 'end', value: string) => {
        if (type === 'start') {
            setStartDate(value);
        } else {
            setEndDate(value);
        }
        
        // Mudar para personalizado quando as datas são alteradas manualmente
        if (activeFilter !== 'personalizado') {
            setActiveFilter('personalizado');
        }
    };

    // Função para exportar dados em CSV
    const exportToCSV = () => {
        if (transactionData.length === 0) {
            alert(__('Não há dados para exportar', 'lkn-integration-rede-for-woocommerce'));
            return;
        }

        // Cabeçalhos das colunas baseados na configuração (apenas colunas visíveis)
        const visibleColumnsConfig = columnConfig.filter(col => col.visible);
        const headers = visibleColumnsConfig.map(col => col.name);

        // Converter dados para CSV baseado na configuração de colunas
        const csvContent = [headers, ...transactionData.map(transaction => 
            visibleColumnsConfig.map(colConfig => {
                let value = '';
                
                // Extrair valor baseado no ID da coluna
                switch (colConfig.id) {
                    case 'gateway':
                        value = transaction.gateway?.masked || 'N/A';
                        break;
                    case 'cvv_sent':
                        value = transaction.transaction?.cvv_sent || 'N/A';
                        break;
                    case 'type':
                        value = transaction.gateway?.type || 'N/A';
                        break;
                    case 'installments':
                        value = getValueOrDefault(transaction.transaction?.installments);
                        break;
                    case 'installment_amount':
                        value = getValueOrDefault(transaction.transaction?.installment_amount);
                        break;
                    case 'brand':
                        value = transaction.gateway?.brand || 'N/A';
                        break;
                    case 'expiry':
                        value = transaction.gateway?.expiry || 'N/A';
                        break;
                    case 'datetime':
                        value = transaction.system?.request_datetime || 'N/A';
                        break;
                    case 'total':
                        value = getValueOrDefault(transaction.amounts?.total);
                        break;
                    case 'subtotal':
                        value = getValueOrDefault(transaction.amounts?.subtotal);
                        break;
                    case 'shipping':
                        value = getValueOrDefault(transaction.amounts?.shipping);
                        break;
                    case 'interest_discount':
                        value = getValueOrDefault(transaction.amounts?.interest_discount);
                        break;
                    case 'currency':
                        value = transaction.amounts?.currency || 'N/A';
                        break;
                    case 'capture':
                        value = transaction.transaction?.capture || 'N/A';
                        break;
                    case 'recurrent':
                        value = transaction.transaction?.recurrent || 'N/A';
                        break;
                    case 'auth_3ds':
                        value = transaction.transaction?.['3ds_auth'] || 'N/A';
                        break;
                    case 'tid':
                        value = transaction.transaction?.tid || 'N/A';
                        break;
                    case 'environment':
                        value = transaction.system?.environment || 'N/A';
                        break;
                    case 'payment_gateway':
                        value = transaction.system?.gateway || 'N/A';
                        break;
                    case 'order_id':
                        value = transaction.system?.order_id || 'N/A';
                        break;
                    case 'reference':
                        value = transaction.system?.reference || 'N/A';
                        break;
                    case 'pv':
                        value = transaction.credentials?.pv_masked || 'N/A';
                        break;
                    case 'token':
                        value = transaction.credentials?.token_masked || 'N/A';
                        break;
                    case 'return_code':
                        value = transaction.response?.return_code || 'N/A';
                        break;
                    case 'http_status':
                        value = transaction.response?.http_status || 'N/A';
                        break;
                    case 'holder_name':
                        value = transaction.gateway?.holder_name || 'N/A';
                        break;
                    default:
                        value = 'N/A';
                }
                
                // Escapar aspas e envolver em aspas se contém vírgula
                return `"${String(value).replace(/"/g, '""')}"`;
            })
        )]
            .map(row => row.join(','))
            .join('\n');

        // Download do arquivo
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `rede-transacoes-${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // Função para exportar dados em XLS (Excel)
    const exportToXLS = () => {
        if (transactionData.length === 0) {
            alert(__('Não há dados para exportar', 'lkn-integration-rede-for-woocommerce'));
            return;
        }

        // Cabeçalhos das colunas baseados na configuração (apenas colunas visíveis)
        const visibleColumnsConfig = columnConfig.filter(col => col.visible);
        const headers = visibleColumnsConfig.map(col => col.name);

        // Gerar HTML table que o Excel pode interpretar
        let xlsContent = '<html><head><meta charset="UTF-8"></head><body><table border="1">';
        
        // Cabeçalho
        xlsContent += '<tr>';
        headers.forEach(header => {
            xlsContent += `<th style="background-color: #f0f0f0; font-weight: bold;">${escapeHtml(header)}</th>`;
        });
        xlsContent += '</tr>';
        
        // Dados baseados na configuração de colunas
        transactionData.forEach(transaction => {
            xlsContent += '<tr>';
            visibleColumnsConfig.forEach(colConfig => {
                let value = '';
                
                // Extrair valor baseado no ID da coluna (mesmo switch do CSV)
                switch (colConfig.id) {
                    case 'gateway':
                        value = transaction.gateway?.masked || 'N/A';
                        break;
                    case 'cvv_sent':
                        value = transaction.transaction?.cvv_sent || 'N/A';
                        break;
                    case 'type':
                        value = transaction.gateway?.type || 'N/A';
                        break;
                    case 'installments':
                        value = getValueOrDefault(transaction.transaction?.installments);
                        break;
                    case 'installment_amount':
                        value = getValueOrDefault(transaction.transaction?.installment_amount);
                        break;
                    case 'brand':
                        value = transaction.gateway?.brand || 'N/A';
                        break;
                    case 'expiry':
                        value = transaction.gateway?.expiry || 'N/A';
                        break;
                    case 'datetime':
                        value = transaction.system?.request_datetime || 'N/A';
                        break;
                    case 'total':
                        value = getValueOrDefault(transaction.amounts?.total);
                        break;
                    case 'subtotal':
                        value = getValueOrDefault(transaction.amounts?.subtotal);
                        break;
                    case 'shipping':
                        value = getValueOrDefault(transaction.amounts?.shipping);
                        break;
                    case 'interest_discount':
                        value = getValueOrDefault(transaction.amounts?.interest_discount);
                        break;
                    case 'currency':
                        value = transaction.amounts?.currency || 'N/A';
                        break;
                    case 'capture':
                        value = transaction.transaction?.capture || 'N/A';
                        break;
                    case 'recurrent':
                        value = transaction.transaction?.recurrent || 'N/A';
                        break;
                    case 'auth_3ds':
                        value = transaction.transaction?.['3ds_auth'] || 'N/A';
                        break;
                    case 'tid':
                        value = transaction.transaction?.tid || 'N/A';
                        break;
                    case 'environment':
                        value = transaction.system?.environment || 'N/A';
                        break;
                    case 'payment_gateway':
                        value = transaction.system?.gateway || 'N/A';
                        break;
                    case 'order_id':
                        value = transaction.system?.order_id || 'N/A';
                        break;
                    case 'reference':
                        value = transaction.system?.reference || 'N/A';
                        break;
                    case 'pv':
                        value = transaction.credentials?.pv_masked || 'N/A';
                        break;
                    case 'token':
                        value = transaction.credentials?.token_masked || 'N/A';
                        break;
                    case 'return_code':
                        value = transaction.response?.return_code || 'N/A';
                        break;
                    case 'http_status':
                        value = transaction.response?.http_status || 'N/A';
                        break;
                    case 'holder_name':
                        value = transaction.gateway?.holder_name || 'N/A';
                        break;
                    default:
                        value = 'N/A';
                }
                
                xlsContent += `<td>${escapeHtml(String(value))}</td>`;
            });
            xlsContent += '</tr>';
        });
        
        xlsContent += '</table></body></html>';

        // Download do arquivo
        const blob = new Blob([xlsContent], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `rede-transacoes-${new Date().toISOString().split('T')[0]}.xls`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // Função auxiliar para escapar caracteres HTML
    const escapeHtml = (text: any): string => {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    // Função auxiliar para verificar se um valor é zero ou se deve ser tratado como N/A
    const getValueOrDefault = (value: any, defaultValue: string = 'N/A'): any => {
        // Se o valor é exatamente 0 (number ou string), retornar '0'
        if (value === 0 || value === '0') {
            return '0';
        }
        // Se o valor existe e não é null/undefined/empty string, retornar o valor
        if (value !== null && value !== undefined && value !== '') {
            return value;
        }
        // Caso contrário, retornar o valor padrão
        return defaultValue;
    };

    // Função para gerar dados da linha baseados na configuração de colunas
    const generateRowData = (transaction: any) => {
        // Se transaction já é um array (formato antigo), usar diretamente
        if (Array.isArray(transaction)) {
            // Mapear array antigo para nova estrutura baseada na configuração
            const originalData = [
                transaction[0] || 'N/A', // Cartão
                transaction[1] || 'N/A', // CVV Enviado
                transaction[2] || 'N/A', // Tipo
                transaction[3] || 'N/A', // Parcelas
                transaction[4] || 'N/A', // Vlr. Parcela
                transaction[5] || 'N/A', // Bandeira
                transaction[6] || 'N/A', // Vencimento
                transaction[7] || 'N/A', // Data/Hora
                transaction[8] || 'N/A', // Total
                transaction[9] || 'N/A', // Subtotal
                transaction[10] || 'N/A', // Frete
                transaction[11] || 'N/A', // Juros/Desc.
                transaction[12] || 'N/A', // Moeda
                transaction[13] || 'N/A', // Captura
                transaction[14] || 'N/A', // Recorrente
                transaction[15] || 'N/A', // 3DS Auth
                transaction[16] || 'N/A', // TID
                transaction[17] || 'N/A', // Ambiente
                transaction[18] || 'N/A', // Gateway
                transaction[19] || 'N/A', // Order ID
                transaction[20] || 'N/A', // Reference
                transaction[21] || 'N/A', // PV
                transaction[22] || 'N/A', // Token
                transaction[23] || 'N/A', // Return Code
                transaction[24] || 'N/A', // HTTP Status
                transaction[25] || 'N/A', // Portador
                transaction[26] || html(
                    `<a href="#" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; padding: 6px 12px; background-color: #25D366; color: white; text-decoration: none; border-radius: 4px; font-size: 12px; font-weight: bold; transition: background-color 0.3s;" title="${escapeHtml(__('Abrir WhatsApp para suporte', 'lkn-integration-rede-for-woocommerce'))}" onmouseover="this.style.backgroundColor='#128C7E'" onmouseout="this.style.backgroundColor='#25D366'">
                        <svg style="width: 16px; height: 16px; margin-right: 4px; fill: currentColor;" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.087z"/>
                        </svg>
                        ${escapeHtml(__('Suporte', 'lkn-integration-rede-for-woocommerce'))}
                    </a>`
                ) // Suporte
            ];

            const rowData: any[] = [];
            const defaultColumns = DEFAULT_COLUMNS;

            columnConfig.forEach(column => {
                if (!column.visible) return;
                
                const columnIndex = defaultColumns.findIndex(col => col.id === column.id);
                if (columnIndex >= 0 && columnIndex < originalData.length) {
                    rowData.push(originalData[columnIndex]);
                } else {
                    rowData.push('N/A');
                }
            });
            
            return rowData;
        }
        
        // Se transaction é objeto (nova estrutura), usar a lógica anterior
        const rowData: any[] = [];
        
        columnConfig.forEach(column => {
            if (!column.visible) return;
            
            let value: any = '';
            
            switch (column.id) {
                case 'gateway':
                    value = (transaction && transaction.gateway && transaction.gateway.masked) ? transaction.gateway.masked : 'N/A';
                    break;
                case 'cvv_sent':
                    value = (transaction && transaction.transaction && transaction.transaction.cvv_sent) ? transaction.transaction.cvv_sent : 'N/A';
                    break;
                case 'type':
                    value = (transaction && transaction.gateway && transaction.gateway.type) ? transaction.gateway.type : 'N/A';
                    break;
                case 'installments':
                    value = getValueOrDefault((transaction && transaction.transaction && transaction.transaction.installments !== undefined) ? transaction.transaction.installments : null);
                    break;
                case 'installment_amount':
                    value = getValueOrDefault((transaction && transaction.transaction && transaction.transaction.installment_amount !== undefined) ? transaction.transaction.installment_amount : null);
                    break;
                case 'brand':
                    value = (transaction && transaction.gateway && transaction.gateway.brand) ? transaction.gateway.brand : 'N/A';
                    break;
                case 'expiry':
                    value = (transaction && transaction.gateway && transaction.gateway.expiry) ? transaction.gateway.expiry : 'N/A';
                    break;
                case 'datetime':
                    value = (transaction && transaction.system && transaction.system.request_datetime) ? transaction.system.request_datetime : 'N/A';
                    break;
                case 'total':
                    value = getValueOrDefault(transaction.amounts?.total);
                    break;
                case 'subtotal':
                    value = getValueOrDefault(transaction.amounts?.subtotal);
                    break;
                case 'shipping':
                    value = getValueOrDefault(transaction.amounts?.shipping);
                    break;
                case 'interest_discount':
                    value = getValueOrDefault(transaction.amounts?.interest_discount);
                    break;
                case 'currency':
                    value = transaction.amounts?.currency || 'N/A';
                    break;
                case 'capture':
                    value = (transaction && transaction.transaction && transaction.transaction.capture) ? transaction.transaction.capture : 'N/A';
                    break;
                case 'recurrent':
                    value = (transaction && transaction.transaction && transaction.transaction.recurrent) ? transaction.transaction.recurrent : 'N/A';
                    break;
                case 'auth_3ds':
                    value = (transaction && transaction.transaction && transaction.transaction['3ds_auth']) ? transaction.transaction['3ds_auth'] : 'N/A';
                    break;
                case 'tid':
                    value = (transaction && transaction.transaction && transaction.transaction.tid) ? transaction.transaction.tid : 'N/A';
                    break;
                case 'environment':
                    value = (transaction && transaction.system && transaction.system.environment) ? transaction.system.environment : 'N/A';
                    break;
                case 'payment_gateway':
                    value = (transaction && transaction.system && transaction.system.gateway) ? transaction.system.gateway : 'N/A';
                    break;
                case 'order_id':
                    value = (transaction && transaction.system && transaction.system.order_id) ? transaction.system.order_id : 'N/A';
                    break;
                case 'reference':
                    value = (transaction && transaction.system && transaction.system.reference) ? transaction.system.reference : 'N/A';
                    break;
                case 'pv':
                    value = (transaction && transaction.credentials && transaction.credentials.pv_masked) ? transaction.credentials.pv_masked : 'N/A';
                    break;
                case 'token':
                    value = (transaction && transaction.credentials && transaction.credentials.token_masked) ? transaction.credentials.token_masked : 'N/A';
                    break;
                case 'return_code':
                    value = (transaction && transaction.response && transaction.response.return_code) ? transaction.response.return_code : 'N/A';
                    break;
                case 'http_status':
                    value = (transaction && transaction.response && transaction.response.http_status) ? transaction.response.http_status : 'N/A';
                    break;
                case 'holder_name':
                    value = (transaction && transaction.gateway && transaction.gateway.holder_name) ? transaction.gateway.holder_name : 'N/A';
                    break;
                case 'whatsapp':
                    value = html(
                        `<a href="${generateWhatsAppLink(transaction)}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; padding: 6px 12px; background-color: #25D366; color: white; text-decoration: none; border-radius: 4px; font-size: 12px; font-weight: bold; transition: background-color 0.3s;" title="${escapeHtml(__('Abrir WhatsApp para suporte', 'lkn-integration-rede-for-woocommerce'))}" onmouseover="this.style.backgroundColor='#128C7E'" onmouseout="this.style.backgroundColor='#25D366'">
                            <svg style="width: 16px; height: 16px; margin-right: 4px; fill: currentColor;" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.087z"/>
                            </svg>
                            ${escapeHtml(__('Suporte', 'lkn-integration-rede-for-woocommerce'))}
                        </a>`
                    );
                    break;
                default:
                    value = 'N/A';
            }
            
            rowData.push(value);
        });
        
        return rowData;
    };

    // Carregar configuração de colunas ao montar o componente
    useEffect(() => {
        setColumnConfig(loadColumnConfig());
        // Garantir que o container de configuração sempre comece fechado
        setShowColumnConfig(false);
    }, []);

    // Buscar dados quando o componente for montado e aplicar filtro "hoje" por padrão
    useEffect(() => {
        setDateFilter('hoje');
    }, []);

    // Configurar e renderizar o Grid quando os dados estiverem prontos
    useEffect(() => {
        if (gridRef.current && !loading) {
            // Gerar colunas baseadas na configuração
            const visibleColumns = columnConfig
                .filter(col => col.visible)
                .map(col => ({
                    name: col.name,
                    resizable: true,
                    sort: true,
                    formatter: (cell: any, row: any, column: any) => {
                        // Para colunas especiais, gerar HTML com tooltips
                        if (col.id === 'brand' && cell !== 'N/A') {
                            return html(generateBrandTooltipHTML(cell));
                        }
                        
                        if ((col.id === 'return_code' || col.id === 'http_status') && cell !== 'N/A') {
                            const label = col.id === 'return_code' ? 'Return Code' : 'HTTP Status';
                            return html(generateCodeTooltipHTML(cell, label));
                        }
                        
                        return cell;
                    }
                }));

            // Gerar dados das linhas baseados na configuração
            const tableData = transactionData.map(transaction => generateRowData(transaction));

            // Configuração do Grid.js
            const grid = new Grid({
                columns: visibleColumns,
                data: tableData,
                search: true,
                sort: true,
                pagination: {
                    limit: perPageLimit
                } as any,
                className: {
                    table: 'rede-transactions-table',
                    header: 'rede-table-header',
                    tbody: 'rede-table-body'
                },
                style: {
                    table: {
                        'white-space': 'nowrap'
                    }
                },
                language: {
                    search: {
                        placeholder: __('Buscar transações...', 'lkn-integration-rede-for-woocommerce')
                    },
                    pagination: {
                        previous: __('Anterior', 'lkn-integration-rede-for-woocommerce'),
                        next: __('Próxima', 'lkn-integration-rede-for-woocommerce'),
                        navigate: (page: number, pages: number) => `${__('Página', 'lkn-integration-rede-for-woocommerce')} ${page} ${__('de', 'lkn-integration-rede-for-woocommerce')} ${pages}`,
                        page: (page: number) => `${__('Página', 'lkn-integration-rede-for-woocommerce')} ${page}`,
                        showing: __('Mostrando', 'lkn-integration-rede-for-woocommerce'),
                        of: __('de', 'lkn-integration-rede-for-woocommerce'),
                        to: __('a', 'lkn-integration-rede-for-woocommerce'),
                        results: () => __('registros', 'lkn-integration-rede-for-woocommerce')
                    },
                    loading: __('Carregando...', 'lkn-integration-rede-for-woocommerce'),
                    noRecordsFound: __('Nenhuma transação encontrada', 'lkn-integration-rede-for-woocommerce'),
                    error: __('Ocorreu um erro ao carregar os dados', 'lkn-integration-rede-for-woocommerce')
                }
            });

            // Renderizar o grid
            grid.render(gridRef.current);
            
            // Adicionar event listeners para tooltips após renderização
            const setupTooltips = () => {
                const tooltipManager = TooltipManager.getInstance();
                const tooltipTriggers = gridRef.current?.querySelectorAll('.tooltip-trigger');
                
                tooltipTriggers?.forEach(trigger => {
                    const element = trigger as HTMLElement;
                    const tooltipText = element.getAttribute('data-tooltip');
                    
                    if (tooltipText) {
                        element.addEventListener('mouseenter', () => {
                            tooltipManager.showTooltip(element, tooltipText);
                        });
                        
                        element.addEventListener('mouseleave', () => {
                            tooltipManager.hideTooltip();
                        });
                    }
                });
            };
            
            // Configurar tooltips após renderização inicial e após mudanças de página
            setTimeout(setupTooltips, 0);
            
            // Re-configurar tooltips quando a paginação mudar
            const observer = new MutationObserver(() => {
                setTimeout(setupTooltips, 0);
            });
            
            if (gridRef.current) {
                observer.observe(gridRef.current, { childList: true, subtree: true });
            }

            // Cleanup
            return () => {
                if (grid) {
                    grid.destroy();
                }
                observer.disconnect();
            };
        }
    }, [transactionData, loading, perPageLimit, columnConfig]); // Dependências: transactionData, loading, perPageLimit e columnConfig

    // Verificar se a licença está inativa para mostrar apenas o screenshot
    const analyticsData = (window as any).lknRedeAnalytics || {};

    if (analyticsData.plugin_license === 'inactive') {
        return (
            <div className="woocommerce-layout">
                <div className="woocommerce-layout__primary">
                    <div className="woocommerce-layout__main">
                        {/* Screenshot da funcionalidade como link */}
                        <a 
                            href={analyticsData.pro_version} 
                            target="_blank" 
                            rel="noopener noreferrer"
                            style={{
                                display: 'block',
                                cursor: 'pointer',
                            }}
                        >
                            <img 
                                src={analyticsData.screenshot_url} 
                                alt={__('Click to upgrade to Rede Analytics PRO', 'lkn-integration-rede-for-woocommerce')}
                                style={{
                                    width: '100%',
                                    height: 'auto',
                                    display: 'block'
                                }}
                            />
                        </a>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="woocommerce-layout">
            <div className="woocommerce-layout__primary">
                <div className="woocommerce-layout__main">
                    {/* Interface de Configuração de Colunas - Fora do container principal */}
                    {showColumnConfig === true && (
                        <div style={{ 
                            marginBottom: '20px', 
                            padding: '20px', 
                            backgroundColor: '#f8f9fa', 
                            borderRadius: '8px',
                            border: '1px solid #dee2e6'
                        }}>
                            <div style={{ 
                                display: 'flex', 
                                justifyContent: 'space-between', 
                                alignItems: 'center', 
                                marginBottom: '15px',
                                flexWrap: 'wrap',
                                gap: '10px'
                            }}>
                                <h3 style={{ 
                                    margin: 0, 
                                    fontSize: '16px',
                                    minWidth: 'max-content'
                                }}>
                                    {__('Configuração de Colunas', 'lkn-integration-rede-for-woocommerce')}
                                </h3>
                                <div style={{ 
                                    display: 'flex', 
                                    gap: '8px',
                                    flexWrap: 'wrap',
                                    justifyContent: window.innerWidth < 768 ? 'center' : 'flex-end'
                                }}>
                                    <button
                                        onClick={resetColumnConfig}
                                        className="button"
                                        style={{ 
                                            padding: '5px 12px', 
                                            fontSize: '12px',
                                            backgroundColor: 'transparent',
                                            color: '#0073aa',
                                            border: '1px solid #0073aa',
                                            borderRadius: '3px'
                                        }}
                                    >
                                        {__('Restaurar Padrão', 'lkn-integration-rede-for-woocommerce')}
                                    </button>
                                    <button
                                        onClick={() => setShowColumnConfig(false)}
                                        className="button"
                                        style={{ 
                                            padding: '5px 12px', 
                                            fontSize: '12px',
                                            backgroundColor: '#0073aa',
                                            color: 'white',
                                            border: '1px solid #0073aa',
                                            borderRadius: '3px'
                                        }}
                                    >
                                        {__('Fechar', 'lkn-integration-rede-for-woocommerce')}
                                    </button>
                                </div>
                            </div>
                            
                            <div className="column-config-grid">
                                {columnConfig.map((column, index) => (
                                    <div
                                        key={column.id}
                                        draggable
                                        onDragStart={(e) => handleDragStart(e, index)}
                                        onDragOver={(e) => handleDragOver(e, index)}
                                        onDragLeave={handleDragLeave}
                                        onDrop={(e) => handleDrop(e, index)}
                                        style={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'space-between',
                                            padding: '10px 12px',
                                            backgroundColor: column.visible ? '#ffffff' : '#f1f3f4',
                                            border: dragOverItem === index ? '2px dashed #0073aa' : '1px solid #e0e0e0',
                                            borderRadius: '6px',
                                            cursor: 'grab',
                                            opacity: draggedItem === index ? 0.5 : 1,
                                            transition: 'all 0.2s ease',
                                            fontSize: '13px',
                                            boxShadow: column.visible ? '0 1px 3px rgba(0,0,0,0.1)' : 'none'
                                        }}
                                    >
                                        <div style={{ display: 'flex', alignItems: 'center', flex: 1, minWidth: 0 }}>
                                            <span style={{ 
                                                marginRight: '8px', 
                                                fontSize: '12px',
                                                color: '#666',
                                                fontWeight: 'bold'
                                            }}>
                                                {index + 1}.
                                            </span>
                                            <label 
                                                style={{ 
                                                    display: 'flex', 
                                                    alignItems: 'center', 
                                                    cursor: 'pointer',
                                                    flex: 1,
                                                    minWidth: 0
                                                }}
                                                title={column.name}
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={column.visible}
                                                    onChange={() => toggleColumnVisibility(index)}
                                                    style={{ marginRight: '8px', marginTop: '0px', marginBottom: '0px' }}
                                                />
                                                <span style={{ 
                                                    textOverflow: 'ellipsis',
                                                    overflow: 'hidden',
                                                    whiteSpace: 'nowrap',
                                                    fontWeight: column.visible ? '500' : '400'
                                                }}>
                                                    {column.name}
                                                </span>
                                            </label>
                                        </div>
                                        <div style={{ display: 'flex', gap: '4px', marginLeft: '8px' }}>
                                            <button
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    moveColumnUp(index);
                                                }}
                                                disabled={index === 0}
                                                style={{
                                                    padding: '4px 6px',
                                                    border: '1px solid #ccc',
                                                    backgroundColor: index === 0 ? '#f5f5f5' : '#fff',
                                                    borderRadius: '3px',
                                                    cursor: index === 0 ? 'not-allowed' : 'pointer',
                                                    fontSize: '10px',
                                                    opacity: index === 0 ? 0.5 : 1
                                                }}
                                                title={__('Mover para cima', 'lkn-integration-rede-for-woocommerce')}
                                            >
                                                ↑
                                            </button>
                                            <button
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    moveColumnDown(index);
                                                }}
                                                disabled={index === columnConfig.length - 1}
                                                style={{
                                                    padding: '4px 6px',
                                                    border: '1px solid #ccc',
                                                    backgroundColor: index === columnConfig.length - 1 ? '#f5f5f5' : '#fff',
                                                    borderRadius: '3px',
                                                    cursor: index === columnConfig.length - 1 ? 'not-allowed' : 'pointer',
                                                    fontSize: '10px',
                                                    opacity: index === columnConfig.length - 1 ? 0.5 : 1
                                                }}
                                                title={__('Mover para baixo', 'lkn-integration-rede-for-woocommerce')}
                                            >
                                                ↓
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            
                            <div style={{ 
                                marginTop: '15px', 
                                padding: '12px', 
                                backgroundColor: '#e3f2fd', 
                                borderRadius: '6px',
                                fontSize: '12px',
                                color: '#1565c0',
                                border: '1px solid #bbdefb'
                            }}>
                                <strong>{__('💡 Dicas:', 'lkn-integration-rede-for-woocommerce')}</strong><br/>
                                • {__('Marque/desmarque as caixas para mostrar/ocultar colunas', 'lkn-integration-rede-for-woocommerce')}<br/>
                                • {__('Use ↑↓ ou arraste os cards para reordenar as colunas', 'lkn-integration-rede-for-woocommerce')}<br/>
                                • {__('As configurações são salvas automaticamente', 'lkn-integration-rede-for-woocommerce')}
                            </div>
                        </div>
                    )}

                    {/* Tabela de Transações */}
                    <div className="woocommerce-card">
                        <div className="woocommerce-card__header">
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '15px', flexWrap: 'wrap', gap: '10px' }}>
                                <h2>{__('Transações Rede', 'lkn-integration-rede-for-woocommerce')}</h2>
                                <div style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
                                    <button
                                        onClick={() => setShowColumnConfig(!showColumnConfig)}
                                        className="button"
                                        style={{ 
                                            padding: '8px 16px', 
                                            fontSize: '14px',
                                            backgroundColor: 'transparent',
                                            color: '#0073aa',
                                            border: '1px solid #0073aa',
                                            borderRadius: '3px',
                                            cursor: 'pointer'
                                        }}
                                        title={__('Configurar ordem e visibilidade das colunas', 'lkn-integration-rede-for-woocommerce')}
                                    >
                                        ⚙️ {__('Configurar Colunas', 'lkn-integration-rede-for-woocommerce')}
                                    </button>
                                    <button
                                        onClick={exportToCSV}
                                        disabled={loading || transactionData.length === 0}
                                        className="button"
                                        style={{ 
                                            padding: '8px 16px', 
                                            fontSize: '14px',
                                            backgroundColor: '#0073aa',
                                            color: 'white',
                                            border: '1px solid #0073aa',
                                            borderRadius: '3px',
                                            cursor: loading || transactionData.length === 0 ? 'not-allowed' : 'pointer',
                                            opacity: loading || transactionData.length === 0 ? 0.6 : 1
                                        }}
                                        title={__('Exportar dados em formato CSV', 'lkn-integration-rede-for-woocommerce')}
                                    >
                                        📄 {__('Exportar CSV', 'lkn-integration-rede-for-woocommerce')}
                                    </button>
                                    <button
                                        onClick={exportToXLS}
                                        disabled={loading || transactionData.length === 0}
                                        className="button"
                                        style={{ 
                                            padding: '8px 16px', 
                                            fontSize: '14px',
                                            backgroundColor: '#217346',
                                            color: 'white',
                                            border: '1px solid #217346',
                                            borderRadius: '3px',
                                            cursor: loading || transactionData.length === 0 ? 'not-allowed' : 'pointer',
                                            opacity: loading || transactionData.length === 0 ? 0.6 : 1
                                        }}
                                        title={__('Exportar dados em formato Excel', 'lkn-integration-rede-for-woocommerce')}
                                    >
                                        📊 {__('Exportar XLS', 'lkn-integration-rede-for-woocommerce')}
                                    </button>
                                </div>
                            </div>
                            
                            {/* Seção de Configuração de Transações */}
                            <div style={{ marginTop: '20px' }}>
                                {/* Título das últimas transações */}
                                <h3 style={{ 
                                    fontSize: '16px', 
                                    fontWeight: '600', 
                                    marginBottom: '15px', 
                                    color: '#1e1e1e',
                                    borderBottom: '1px solid #ddd',
                                    paddingBottom: '5px'
                                }}>
                                    {__('Últimas transações:', 'lkn-integration-rede-for-woocommerce')}
                                </h3>
                                
                                {/* Limite de consultas por sessão */}
                                <div style={{ marginBottom: '15px' }}>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '10px', flexWrap: 'wrap' }}>
                                        <label htmlFor="query-limit-input" style={{ fontSize: '14px', fontWeight: '500', color: '#666' }}>
                                            {__('Carregar até:', 'lkn-integration-rede-for-woocommerce')}
                                        </label>
                                        <input
                                            id="query-limit-input"
                                            type="number"
                                            value={queryLimit}
                                            onChange={(e) => setQueryLimit(Math.max(1, parseInt(e.target.value) || 1))}
                                            min="1"
                                            max="1000"
                                            style={{ 
                                                padding: '5px', 
                                                border: '1px solid #ddd', 
                                                borderRadius: '4px',
                                                width: '80px'
                                            }}
                                        />
                                    </div>
                                </div>
                                
                                {/* Datas da consulta */}
                                <div style={{ marginBottom: '15px' }}>
                                    <h4 style={{ 
                                        fontSize: '14px', 
                                        fontWeight: '500', 
                                        marginBottom: '10px',
                                        color: '#666'
                                    }}>
                                        {__('Datas da consulta:', 'lkn-integration-rede-for-woocommerce')}
                                    </h4>
                                    <form onSubmit={(e) => { e.preventDefault(); applyDateFilters(); }} style={{ display: 'flex', gap: '10px', alignItems: 'center', flexWrap: 'wrap' }}>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '5px' }}>
                                            <label style={{ fontSize: '14px', fontWeight: '500' }}>
                                                {__('Data Inicial:', 'lkn-integration-rede-for-woocommerce')}
                                            </label>
                                            <input
                                                type="date"
                                                value={startDate}
                                                onChange={(e) => handleDateChange('start', e.target.value)}
                                                style={{ padding: '5px', border: '1px solid #ddd', borderRadius: '4px' }}
                                            />
                                        </div>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '5px' }}>
                                            <label style={{ fontSize: '14px', fontWeight: '500' }}>
                                                {__('Data Final:', 'lkn-integration-rede-for-woocommerce')}
                                            </label>
                                            <input
                                                type="date"
                                                value={endDate}
                                                onChange={(e) => handleDateChange('end', e.target.value)}
                                                style={{ padding: '5px', border: '1px solid #ddd', borderRadius: '4px' }}
                                            />
                                        </div>
                                        <button
                                            type="submit"
                                            disabled={loading}
                                            className="button button-primary"
                                            style={{ padding: '5px 15px', fontSize: '14px' }}
                                        >
                                            {__('Filtrar', 'lkn-integration-rede-for-woocommerce')}
                                        </button>
                                    </form>
                                </div>
                                
                                {/* Linha divisória */}
                                <hr style={{ margin: '20px 0', border: 'none', borderTop: '1px solid #ddd' }} />
                                
                                {/* Controles de filtro e paginação em linha */}
                                <div style={{ 
                                    display: 'flex', 
                                    justifyContent: 'space-between', 
                                    alignItems: 'flex-start', 
                                    gap: '20px', 
                                    flexWrap: 'wrap'
                                }}>
                                    {/* Botões de filtro rápido */}
                                    <div style={{ flex: '1', minWidth: '200px' }}>
                                        <div style={{ display: 'flex', gap: '8px', alignItems: 'center', flexWrap: 'wrap' }}>
                                            <button
                                                onClick={() => setDateFilter('hoje')}
                                                className={`button ${activeFilter === 'hoje' ? 'button-primary' : ''}`}
                                                style={{ padding: '6px 12px', fontSize: '13px' }}
                                            >
                                                {__('Hoje', 'lkn-integration-rede-for-woocommerce')}
                                            </button>
                                            <button
                                                onClick={() => setDateFilter('7dias')}
                                                className={`button ${activeFilter === '7dias' ? 'button-primary' : ''}`}
                                                style={{ padding: '6px 12px', fontSize: '13px' }}
                                            >
                                                {__('Últimos 7 dias', 'lkn-integration-rede-for-woocommerce')}
                                            </button>
                                            <button
                                                onClick={() => setDateFilter('30dias')}
                                                className={`button ${activeFilter === '30dias' ? 'button-primary' : ''}`}
                                                style={{ padding: '6px 12px', fontSize: '13px' }}
                                            >
                                                {__('Últimos 30 dias', 'lkn-integration-rede-for-woocommerce')}
                                            </button>
                                            <button
                                                onClick={() => setActiveFilter('personalizado')}
                                                className={`button ${activeFilter === 'personalizado' ? 'button-primary' : ''}`}
                                                style={{ 
                                                    padding: '6px 12px', 
                                                    fontSize: '13px',
                                                    opacity: activeFilter !== 'personalizado' ? 0.6 : 1,
                                                    cursor: activeFilter !== 'personalizado' ? 'not-allowed' : 'pointer'
                                                }}
                                                disabled={activeFilter !== 'personalizado'}
                                            >
                                                {__('Personalizado', 'lkn-integration-rede-for-woocommerce')}
                                            </button>
                                            <button
                                                onClick={clearDateFilters}
                                                disabled={loading}
                                                className="button"
                                                style={{ padding: '6px 12px', fontSize: '13px' }}
                                            >
                                                {__('Restaurar Padrão', 'lkn-integration-rede-for-woocommerce')}
                                            </button>
                                        </div>
                                    </div>
                                    
                                    {/* Transações por página */}
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px', whiteSpace: 'nowrap' }}>
                                        <label htmlFor="per-page-limit-input" style={{ fontSize: '14px', fontWeight: '500' }}>
                                            {__('Exibição por página:', 'lkn-integration-rede-for-woocommerce')}
                                        </label>
                                        <input
                                            id="per-page-limit-input"
                                            type="number"
                                            value={perPageLimit}
                                            onChange={(e) => setPerPageLimit(Math.max(1, parseInt(e.target.value) || 1))}
                                            min="1"
                                            max="100"
                                            style={{ 
                                                padding: '5px', 
                                                border: '1px solid #ddd', 
                                                borderRadius: '4px',
                                                width: '80px'
                                            }}
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="woocommerce-card__body">
                            {loading && (
                                <div className="loading-indicator">
                                    <p>{__('Carregando transações...', 'lkn-integration-rede-for-woocommerce')}</p>
                                </div>
                            )}
                            {error && (
                                <div className="error-message">
                                    <p>{__('Erro:', 'lkn-integration-rede-for-woocommerce')} {error}</p>
                                    <button onClick={() => fetchTransactionData(1)} className="button">
                                        {__('Tentar novamente', 'lkn-integration-rede-for-woocommerce')}
                                    </button>
                                </div>
                            )}
                            {!loading && !error && (
                                <>
                                    {/* Informações de paginação */}
                                    <div style={{ marginBottom: '15px', fontSize: '14px', color: '#666', padding: '10px', backgroundColor: '#f9f9f9', borderRadius: '4px' }}>
                                        {__('Mostrando', 'lkn-integration-rede-for-woocommerce')} {transactionData.length} {__('do total de', 'lkn-integration-rede-for-woocommerce')} {totalCount} {__('transações', 'lkn-integration-rede-for-woocommerce')}
                                        {currentPage > 1 && (
                                            <span style={{ marginLeft: '10px' }}>
                                                ({__('Página', 'lkn-integration-rede-for-woocommerce')} {currentPage})
                                            </span>
                                        )}
                                    </div>
                                    
                                    <div ref={gridRef} className="rede-grid-container"></div>
                                    
                                    {/* Botão carregar mais */}
                                    {hasNextPage && (
                                        <div style={{ textAlign: 'center', marginTop: '20px', padding: '15px' }}>
                                            <button 
                                                onClick={loadMoreData}
                                                disabled={loadingMore}
                                                className="button button-primary"
                                                style={{
                                                    padding: '10px 20px',
                                                    fontSize: '14px',
                                                    cursor: loadingMore ? 'not-allowed' : 'pointer',
                                                    opacity: loadingMore ? 0.6 : 1
                                                }}
                                            >
                                                {loadingMore ? __('Carregando...', 'lkn-integration-rede-for-woocommerce') : __('Carregar mais transações', 'lkn-integration-rede-for-woocommerce')}
                                            </button>
                                        </div>
                                    )}
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

// Registra a página nos filtros do WooCommerce Admin
function initRedeAnalytics() {
    // Registra a página nos relatórios do WooCommerce Admin
    addFilter(
        'woocommerce_admin_reports_list',
        'rede-transactions',
        (reports) => [
            ...reports,
            {
                report: 'rede-transactions',
                title: __('Rede Transações', 'lkn-integration-rede-for-woocommerce'),
                component: RedeAnalyticsPage
            }
        ]
    );

    // Registra a página no sistema de roteamento do WooCommerce Admin
    addFilter(
        'woocommerce_admin_pages',
        'rede-transactions',
        (pages) => [
            ...pages,
            {
                container: RedeAnalyticsPage,
                path: '/analytics/rede-transactions',
                wpOpenMenu: 'toplevel_page_woocommerce',
                capability: 'view_woocommerce_reports',
                navArgs: {
                    id: 'woocommerce-analytics-rede-transactions'
                }
            }
        ]
    );
}

// Inicializa a extensão
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRedeAnalytics);
} else {
    initRedeAnalytics();
}

export default RedeAnalyticsPage;