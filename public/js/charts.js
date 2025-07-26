/**
 * Chart utilities for dashboard and reports
 */

window.ChartUtils = {
    // Create a simple bar chart
    createBarChart: function(containerId, data, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const maxValue = Math.max(...data.map(item => item.value));
        const chartHeight = options.height || 200;
        
        let html = '<div class="chart-container" style="height: ' + chartHeight + 'px;">';
        
        data.forEach((item, index) => {
            const barHeight = (item.value / maxValue) * (chartHeight - 40);
            const color = options.colors ? options.colors[index % options.colors.length] : '#3b82f6';
            
            html += `
                <div class="chart-bar" style="display: inline-block; width: ${100/data.length}%; vertical-align: bottom; text-align: center; margin-right: 2px;">
                    <div style="height: ${barHeight}px; background-color: ${color}; margin-bottom: 5px; border-radius: 2px;"></div>
                    <div style="font-size: 12px; color: #666;">${item.label}</div>
                    <div style="font-size: 10px; color: #999;">${item.value}</div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    },
    
    // Create a simple pie chart (using CSS)
    createPieChart: function(containerId, data, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const total = data.reduce((sum, item) => sum + item.value, 0);
        const colors = options.colors || ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
        
        let html = '<div class="pie-chart-container flex items-center">';
        
        // Create pie chart using conic-gradient
        let gradientStops = [];
        let currentPercentage = 0;
        
        data.forEach((item, index) => {
            const percentage = (item.value / total) * 100;
            const color = colors[index % colors.length];
            
            gradientStops.push(`${color} ${currentPercentage}% ${currentPercentage + percentage}%`);
            currentPercentage += percentage;
        });
        
        html += `
            <div class="pie-chart" style="
                width: 150px; 
                height: 150px; 
                border-radius: 50%; 
                background: conic-gradient(${gradientStops.join(', ')});
                margin-right: 20px;
            "></div>
        `;
        
        // Legend
        html += '<div class="pie-legend">';
        data.forEach((item, index) => {
            const color = colors[index % colors.length];
            const percentage = ((item.value / total) * 100).toFixed(1);
            
            html += `
                <div class="legend-item flex items-center mb-2">
                    <div style="width: 12px; height: 12px; background-color: ${color}; margin-right: 8px; border-radius: 2px;"></div>
                    <span class="text-sm text-gray-700">${item.label}: ${item.value} (${percentage}%)</span>
                </div>
            `;
        });
        html += '</div>';
        
        html += '</div>';
        container.innerHTML = html;
    },
    
    // Create a simple line chart
    createLineChart: function(containerId, data, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const width = options.width || container.offsetWidth || 400;
        const height = options.height || 200;
        const padding = 40;
        
        const maxValue = Math.max(...data.map(item => item.value));
        const minValue = Math.min(...data.map(item => item.value));
        
        let svg = `<svg width="${width}" height="${height}" style="border: 1px solid #e5e7eb;">`;
        
        // Draw axes
        svg += `<line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" stroke="#d1d5db" stroke-width="1"/>`;
        svg += `<line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}" stroke="#d1d5db" stroke-width="1"/>`;
        
        // Draw data points and lines
        const stepX = (width - 2 * padding) / (data.length - 1);
        let pathData = '';
        
        data.forEach((item, index) => {
            const x = padding + (index * stepX);
            const y = height - padding - ((item.value - minValue) / (maxValue - minValue)) * (height - 2 * padding);
            
            if (index === 0) {
                pathData += `M ${x} ${y}`;
            } else {
                pathData += ` L ${x} ${y}`;
            }
            
            // Draw point
            svg += `<circle cx="${x}" cy="${y}" r="3" fill="#3b82f6"/>`;
            
            // Draw label
            svg += `<text x="${x}" y="${height - 10}" text-anchor="middle" font-size="10" fill="#666">${item.label}</text>`;
        });
        
        // Draw line
        svg += `<path d="${pathData}" stroke="#3b82f6" stroke-width="2" fill="none"/>`;
        
        svg += '</svg>';
        container.innerHTML = svg;
    },
    
    // Create progress bar
    createProgressBar: function(containerId, percentage, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const color = options.color || '#3b82f6';
        const height = options.height || 20;
        const showLabel = options.showLabel !== false;
        
        let html = `
            <div class="progress-bar-container">
                <div class="progress-bar-track" style="
                    width: 100%; 
                    height: ${height}px; 
                    background-color: #e5e7eb; 
                    border-radius: ${height/2}px;
                    position: relative;
                    overflow: hidden;
                ">
                    <div class="progress-bar-fill" style="
                        width: ${Math.min(100, Math.max(0, percentage))}%; 
                        height: 100%; 
                        background-color: ${color}; 
                        transition: width 0.3s ease;
                        border-radius: ${height/2}px;
                    "></div>
                </div>
        `;
        
        if (showLabel) {
            html += `<div class="progress-label text-sm text-gray-600 mt-1">${percentage.toFixed(1)}%</div>`;
        }
        
        html += '</div>';
        container.innerHTML = html;
    },
    
    // Create gauge chart
    createGaugeChart: function(containerId, value, maxValue, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const size = options.size || 150;
        const strokeWidth = options.strokeWidth || 10;
        const radius = (size - strokeWidth) / 2;
        const circumference = 2 * Math.PI * radius;
        const percentage = (value / maxValue) * 100;
        const strokeDasharray = circumference;
        const strokeDashoffset = circumference - (percentage / 100) * circumference;
        
        const color = options.color || '#3b82f6';
        const backgroundColor = options.backgroundColor || '#e5e7eb';
        
        let html = `
            <div class="gauge-chart" style="position: relative; width: ${size}px; height: ${size}px;">
                <svg width="${size}" height="${size}" style="transform: rotate(-90deg);">
                    <circle
                        cx="${size/2}"
                        cy="${size/2}"
                        r="${radius}"
                        stroke="${backgroundColor}"
                        stroke-width="${strokeWidth}"
                        fill="none"
                    />
                    <circle
                        cx="${size/2}"
                        cy="${size/2}"
                        r="${radius}"
                        stroke="${color}"
                        stroke-width="${strokeWidth}"
                        fill="none"
                        stroke-dasharray="${strokeDasharray}"
                        stroke-dashoffset="${strokeDashoffset}"
                        stroke-linecap="round"
                        style="transition: stroke-dashoffset 0.5s ease;"
                    />
                </svg>
                <div style="
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    text-align: center;
                ">
                    <div style="font-size: 18px; font-weight: bold; color: #374151;">${value}</div>
                    <div style="font-size: 12px; color: #6b7280;">of ${maxValue}</div>
                </div>
            </div>
        `;
        
        container.innerHTML = html;
    }
};

// Dashboard chart initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts if chart containers exist
    initializeDashboardCharts();
});

function initializeDashboardCharts() {
    // Employee distribution chart
    const empDistContainer = document.getElementById('employee-distribution-chart');
    if (empDistContainer) {
        // Sample data - in real implementation, this would come from the server
        const empDistData = [
            { label: 'IT', value: 15 },
            { label: 'HR', value: 5 },
            { label: 'Finance', value: 8 },
            { label: 'Marketing', value: 6 },
            { label: 'Operations', value: 10 }
        ];
        
        ChartUtils.createBarChart('employee-distribution-chart', empDistData, {
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
        });
    }
    
    // Salary trends chart
    const salaryTrendsContainer = document.getElementById('salary-trends-chart');
    if (salaryTrendsContainer) {
        const salaryTrendsData = [
            { label: 'Jan', value: 450000 },
            { label: 'Feb', value: 465000 },
            { label: 'Mar', value: 470000 },
            { label: 'Apr', value: 485000 },
            { label: 'May', value: 490000 },
            { label: 'Jun', value: 495000 }
        ];
        
        ChartUtils.createLineChart('salary-trends-chart', salaryTrendsData);
    }
    
    // Attendance overview gauge
    const attendanceGaugeContainer = document.getElementById('attendance-gauge');
    if (attendanceGaugeContainer) {
        ChartUtils.createGaugeChart('attendance-gauge', 85, 100, {
            color: '#10b981',
            size: 120
        });
    }
}

// Export for global access
window.Charts = ChartUtils;