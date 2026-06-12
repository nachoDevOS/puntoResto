import { useToast } from './useToast';

const DEFAULT_TIMEOUT = 2000;

export function useTicketPrinter() {
    const toast = useToast();

    const printTicket = async (printer, sale) => {
        if (!sale || !printer?.ticket_service_url) {
            return;
        }

        const printServiceUrl = printer.ticket_service_url;
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), DEFAULT_TIMEOUT);

        try {
            await fetch(printServiceUrl, { signal: controller.signal, mode: 'no-cors' });
            clearTimeout(timeoutId);

            const details = sale.sale_details.map((detail) => {
                const quantity = Number.parseFloat(detail.quantity);

                return {
                    quantity: Number.isInteger(quantity) ? Number.parseInt(quantity, 10) : quantity,
                    product: detail.item?.name ?? '',
                    total: Number.parseFloat(detail.amount),
                };
            });

            await fetch(`${printServiceUrl}/print`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    ip: printer.type !== 'usb' ? printer.ip : null,
                    port: printer.type !== 'usb' ? printer.port : null,
                    template: printer.template,
                    title: printer.title,
                    sale_number: sale.ticket,
                    sale_type: sale.typeSale,
                    details,
                    observation: sale.observation,
                    date: sale.created_at,
                }),
            });

            toast.success('Imprimiendo ticket...');
        } catch (error) {
            clearTimeout(timeoutId);
            console.error(`No se pudo conectar al servicio de impresión en ${printServiceUrl}.`, error);
            toast.warning('No se pudo conectar al servicio de impresión.');
        }
    };

    return {
        printTicket,
    };
}
