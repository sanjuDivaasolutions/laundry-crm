/**
 * Format currency amount
 * @param {number} amount - The amount to format
 * @param {string} currency - Currency code (default: USD)
 * @param {string} locale - Locale for formatting (default: en-US)
 * @returns {string} Formatted currency string
 */
export function formatCurrency(amount, currency = 'USD', locale = 'en-US') {
  if (amount === null || amount === undefined) {
    return '0.00'
  }
  
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

/**
 * Format number with thousands separator
 * @param {number} number - The number to format
 * @param {string} locale - Locale for formatting (default: en-US)
 * @returns {string} Formatted number string
 */
export function formatNumber(number, locale = 'en-US') {
  if (number === null || number === undefined) {
    return '0'
  }
  
  return new Intl.NumberFormat(locale).format(number)
}