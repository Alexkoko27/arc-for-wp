// Arc for WP - Payment button handler (fixed version)
async function buyWithArc(productId, price, title) {
    const btn = event.currentTarget;
    const originalText = btn.innerHTML || btn.textContent;

    btn.disabled = true;
    btn.textContent = 'Connecting...';

    try {
        if (typeof ethers === 'undefined') {
            throw new Error('Ethers.js not loaded. Please refresh the page.');
        }

        if (!window.ethereum) {
            throw new Error('No crypto wallet detected. Please install MetaMask or Rabby.');
        }

        // Connect wallet
        await window.ethereum.request({ method: 'eth_requestAccounts' });

        const provider = new ethers.BrowserProvider(window.ethereum);
        const signer = await provider.getSigner();
        const buyerAddress = await signer.getAddress();

        // Switch to Arc Testnet
        try {
            await window.ethereum.request({
                method: 'wallet_switchEthereumChain',
                params: [{ chainId: '0x' + arcParams.chainId.toString(16) }]
            });
        } catch (e) {
            console.warn('Could not switch network automatically');
        }

        const usdc = new ethers.Contract(arcParams.usdcAddress, [
            "function transfer(address to, uint256 amount) returns (bool)"
        ], signer);

        const amount = ethers.parseUnits(price.toString(), 6);

        btn.textContent = 'Confirm in wallet...';

        // TODO: merchant address should come from settings
        const merchantAddress = arcParams.merchantAddress || "0x0000000000000000000000000000000000000000";

        const tx = await usdc.transfer(merchantAddress, amount);
        const receipt = await tx.wait();

        // Save order to database
        await jQuery.post(arcParams.ajaxurl, {
            action: 'arc_save_order',
            nonce: arcParams.nonce,
            product_id: productId,
            title: title,
            price: price,
            buyer_address: buyerAddress,
            tx_hash: receipt.hash
        });

        alert(`✅ Payment successful!\n\nProduct: ${title}\nAmount: ${price} USDC\n\nTx: ${arcParams.explorer}/tx/${receipt.hash}`);

    } catch (error) {
        console.error(error);
        let message = error.message || 'Unknown error occurred';
        alert('❌ Payment failed: ' + message);
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
}